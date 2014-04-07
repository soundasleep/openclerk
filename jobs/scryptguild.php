<?php

/**
 * ScryptGuild mining pool balance job (#90).
 */

$exchange = "scryptguild";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_scryptguild WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

crypto_log($account['api_key']);
$data = crypto_json_decode(crypto_get_contents(crypto_wrap_url("http://www.scryptguild.com/api.php?balances=all&workers=all&api_key=" . urlencode($account['api_key']))));

if (!isset($data['balances']) && !isset($data['balances']['earnings'])) {
	throw new ExternalAPIException("No valid response received: check API key");
}

$wallets = get_supported_wallets();
foreach ($wallets['scryptguild'] as $currency) {
	if ($currency == 'hash') {
		// this is just a marker
		continue;
	}

	if (!isset($data['balances']['earnings'][strtolower(get_currency_abbr($currency))])) {
		crypto_log("Found no $currency balance");
		continue;
	}

	$balance = $data['balances']['earnings'][strtolower(get_currency_abbr($currency))]
		+ $data['balances']['adjustments'][strtolower(get_currency_abbr($currency))]
		+ $data['balances']['conversions'][strtolower(get_currency_abbr($currency))]
		- $data['balances']['payouts'][strtolower(get_currency_abbr($currency))];

	insert_new_balance($job, $account, $exchange, $currency, $balance);

}

$hashrate = 0;
foreach ($data['worker_stats'] as $worker) {
	$hashrate .= $worker['speed'];
}

insert_new_hashrate($job, $account, $exchange, 'ltc' /* assume ltc */, $hashrate / 1000 /* in khash */);
