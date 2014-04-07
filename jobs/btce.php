<?php

/**
 * BTC-e balance job.
 */

$exchange = "btce";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_btce WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

require(__DIR__ . "/_btce.php");

$get_supported_wallets = get_supported_wallets();
$currencies = $get_supported_wallets['btce']; // also supports rur, eur, nvc, trc, ppc, nvc
$btce_info = btce_query($account['api_key'], $account['api_secret'], "getInfo");
if (isset($btce_info['error'])) {
	throw new ExternalAPIException("API returned error: '" . $btce_info['error'] . "'");
}
foreach ($currencies as $currency) {
	crypto_log($exchange . " balance for " . $currency . ": " . $btce_info['return']['funds'][$currency]);
	if (!isset($btce_info['return']['funds'][$currency])) {
		throw new ExternalAPIException("Did not find funds for currency $currency in $exchange");
	}

	$balance = $btce_info['return']['funds'][$currency];
	insert_new_balance($job, $account, $exchange, $currency, $balance);

}
