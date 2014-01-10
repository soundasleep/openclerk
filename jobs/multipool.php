<?php

/**
 * Multipool mining pool balance job.
 */

$exchange = "multipool";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_multipool WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

crypto_log($account['api_key']);
$data = crypto_json_decode(crypto_get_contents(crypto_wrap_url("http://api.multipool.us/api.php?api_key=" . urlencode($account['api_key']))));

if (!isset($data['currency']) || !$data['currency']) {
	throw new ExternalAPIException("No valid response received: check API key");
}

$wallets = get_supported_wallets();
foreach ($wallets['multipool'] as $currency) {
	if ($currency == 'hash') {
		// this is just a marker
		continue;
	}

	// assuming that all hashrates are depending on the currency, e.g. BTC returns MH and LTC returns KH
	if (isset($data['currency'][strtolower(get_currency_abbr($currency))])) {

		// also: "btc":{"confirmed_rewards":"0","hashrate":"0","estimated_rewards":0,"payout_history":"0","pool_hashrate":13173361,"round_shares":false,"block_shares":"207893650"}

		insert_new_balance($job, $account, $exchange, $currency, $data['currency'][strtolower(get_currency_abbr($currency))]['confirmed_rewards']);
		insert_new_hashrate($job, $account, $exchange, $currency, $data['currency'][strtolower(get_currency_abbr($currency))]['confirmed_rewards'] / (is_hashrate_mhash($currency) ? 1 : 1000));

	} else {
		crypto_log("Found no $currency balance");
	}
}
