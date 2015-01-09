<?php

/**
 * ypool.net pool balance job.
 */

$exchange = "ypool";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_ypool WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// for each supported currency
$wallets = get_supported_wallets();
foreach ($wallets['ypool'] as $currency) {

	$data = crypto_json_decode(crypto_get_contents(crypto_wrap_url("http://mining.ypool.net/api/personal_stats?coinType=" . urlencode(strtolower(get_currency_abbr($currency))) . "&key=" . urlencode($account['api_key']))));

	// some quick checks
	if (!isset($data['status_code'])) {
		throw new ExternalAPIException("No status code found for currency " . get_currency_abbr($currency));
	}
	if ($data['status_code'] == -5) {
		throw new ExternalAPIException("Invalid currency " . get_currency_abbr($currency));
	}
	if ($data['status_code'] == -3) {
		throw new ExternalAPIException("Invalid API key");
	}

	if (!isset($data['balance'])) {
		throw new ExternalAPIException("No confirmed " . get_currency_abbr($currency) . " reward found");
	}
	// {"status_code":1,"balance":0.00000000,"unconfirmedBalance":0.00000000,"shareValueCurrentRound":0.0000,"foundBlocksOverall":0,"donation":0.0,"autoPayoutAmount":0.000,"paymentAddress":""

	insert_new_balance($job, $account, $exchange, $currency, $data['balance']);
	// the API does not return hashrates, only share rates

}
