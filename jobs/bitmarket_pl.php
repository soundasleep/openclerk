<?php

/**
 * BitMarket.pl balance job.
 */

require(__DIR__ . "/_bitmarket_pl.php");

$exchange = "bitmarket_pl";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_bitmarket_pl WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$info = bitmarket_pl_query($account['api_key'], $account['api_secret'], "info");

if (isset($info['error'])) {
	if (isset($info['errorMsg'])) {
		throw new ExternalAPIException("API returned error: '" . htmlspecialchars($info['errorMsg']) . "'");
	} else {
		throw new ExternalAPIException("API returned error " . htmlspecialchars($info['error']) . "");
	}
}

$get_supported_wallets = get_supported_wallets();
$currencies = $get_supported_wallets['bitmarket_pl']; // btc, pln, ...

foreach ($currencies as $currency) {
	if (!isset($info['data']['balances']['available'][get_currency_abbr($currency)])) {
		// this shouldn't ever happen
		throw new ExternalAPIException("Did not find any " . get_currency_abbr($currency) . " available balance in response");
	}

	$balance = $info['data']['balances']['available'][get_currency_abbr($currency)] + $info['data']['balances']['blocked'][get_currency_abbr($currency)];
	crypto_log($exchange . " balance for " . $currency . ": " . $balance);
	insert_new_balance($job, $account, $exchange, $currency, $balance);

}
