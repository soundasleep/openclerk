<?php

/**
 * ANXPRO balance job.
 */

require(__DIR__ . "/_anxpro.php");

$exchange = "anxpro";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_anxpro WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$info = anxpro_query($account['api_key'], $account['api_secret'], 'money/info');
if (isset($info['error'])) {
	throw new ExternalAPIException("API returned error: '" . $info['error'] . "'");
}

$wallets = get_supported_wallets();
foreach ($wallets['anxpro'] as $currency) {
	if (!isset($info['data']['Wallets'][get_currency_abbr($currency)])) {
		throw new ExternalAPIException("Did not find any wallet for " . get_currency_abbr($currency));
	}
	if (!isset($info['data']['Wallets'][get_currency_abbr($currency)]['Balance']['value'])) {
		throw new ExternalAPIException("Did not find any balance for " . get_currency_abbr($currency));
	}

	// also available: Available_Balance, Daily_Withdrawl_Limit, Max_Withdraw
	$balance = $info['data']['Wallets'][get_currency_abbr($currency)]['Balance']['value'];

	insert_new_balance($job, $account, $exchange, $currency, $balance);

}
