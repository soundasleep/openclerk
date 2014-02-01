<?php

/**
 * Vircurex balance job.
 */

$exchange = "vircurex";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_vircurex WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// from documentation somewhere
$vircurex_balance_count = rand(0,0xffff);
function vircurex_balance($username, $currency, $secret) {
	global $vircurex_balance_count;

	$currency = get_currency_abbr($currency);
	$timestamp = gmdate('Y-m-d\\TH:i:s'); // UTC time
	$id = md5(time() . "_" . rand(0,9999) . "_" . $vircurex_balance_count++);
	$token = hash('sha256', $secret . ";" . $username . ";" . $timestamp . ";" . $id . ";" . "get_balance" . ";" . $currency);
	$url = "https://api.vircurex.com/api/get_balance.json?account=" . urlencode($username) . "&id=" . urlencode($id) . "&token=" . urlencode($token) . "&timestamp=" . urlencode($timestamp) . "&currency=" . urlencode($currency);

	return crypto_json_decode(crypto_get_contents(crypto_wrap_url($url)));
}

$get_supported_wallets = get_supported_wallets();
$currencies = $get_supported_wallets['vircurex']; // also supports rur, eur, nvc, trc, ppc, ...

foreach ($currencies as $i => $currency) {
	if ($i != 0) {
		set_time_limit(30 + (get_site_config('sleep_vircurex_balance') * 2));
		sleep(get_site_config('sleep_vircurex_balance'));
	}

	$balance = vircurex_balance($account['api_username'], $currency, $account['api_secret']);

	// if auth fails, display helpful message
	if (!isset($balance["currency"]) && isset($balance["statustxt"])) {
		throw new ExternalAPIException(htmlspecialchars($balance["statustxt"]));
	}
	if (!isset($balance["currency"]) && isset($balance["statustext"])) {
		throw new ExternalAPIException(htmlspecialchars($balance["statustext"]));
	}

	// sanity check
	if ($balance["currency"] !== get_currency_abbr($currency)) {
		throw new ExternalAPIException("Unexpected currency response from Vircurex: Expected '" . get_currency_abbr($currency) . "', was '" . htmlspecialchars($balance["currency"]) . "'");
	}

	insert_new_balance($job, $account, $exchange, $currency, $balance['balance']);

}

