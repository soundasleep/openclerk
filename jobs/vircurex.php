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

	$currency = strtoupper($currency);
	$timestamp = gmdate('Y-m-d\\TH:i:s'); // UTC time
	$id = md5(time() . "_" . rand(0,9999) . "_" . $vircurex_balance_count++);
	$token = hash('sha256', $secret . ";" . $username . ";" . $timestamp . ";" . $id . ";" . "get_balance" . ";" . $currency);
	$url = "https://vircurex.com/api/get_balance.json?account=" . urlencode($username) . "&id=" . urlencode($id) . "&token=" . urlencode($token) . "&timestamp=" . urlencode($timestamp) . "&currency=" . urlencode($currency);

	return json_decode(crypto_get_contents(crypto_wrap_url($url)), true);
}

$currencies = array('nmc', 'btc', 'ltc', 'usd');

foreach ($currencies as $i => $currency) {
	if ($i != 0) {
		sleep(get_site_config('sleep_vircurex_balance'));
	}

	$balance = vircurex_balance($account['api_username'], $currency, $account['api_secret']);
	if (!$balance) {
		throw new ExternalAPIException("Invalid JSON detected.");
	}

	crypto_log($exchange . " balance for " . $currency . ": " . htmlspecialchars($balance["balance"]));

	// if auth fails, display helpful message
	if (!isset($balance["currency"]) && isset($balance["statustxt"])) {
		throw new ExternalAPIException(htmlspecialchars($balance["statustxt"]));
	}

	// sanity check
	if ($balance["currency"] !== strtoupper($currency)) {
		throw new ExternalAPIException("Unexpected currency response from Vircurex: Expected '" . strtoupper($currency) . "', was '" . htmlspecialchars($balance["currency"]) . "'");
	}

	// disable old instances
	$q = db()->prepare("UPDATE balances SET is_recent=0 WHERE is_recent=1 AND user_id=:user_id AND exchange=:exchange AND currency=:currency AND account_id=:account_id");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
	));

	// we have a balance; update the database
	$q = db()->prepare("INSERT INTO balances SET user_id=:user_id, exchange=:exchange, account_id=:account_id, balance=:balance, currency=:currency, is_recent=1");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
		"balance" => $balance["balance"],
	));
	crypto_log("Inserted new $exchange $currency balances id=" . db()->lastInsertId());
}

