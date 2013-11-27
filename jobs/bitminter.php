<?php

/**
 * BitMinter balance job.
 */

$exchange = "bitminter";

function bitminter_query($url, $headers = array()) {

	// our curl handle (initialize if required)
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BitMinter PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($url));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	if ($res === "Access denied") {
		throw new ExternalAPIException("API response: Access denied");
	}
	$dec = json_decode($res, true);
	if (!$dec) throw new ExternalAPIException('Invalid data received, please make sure connection is working and requested API exists');
	if (isset($dec['message'])) {
		throw new ExternalAPIException(htmlspecialchars($dec['message']));
	}
	return $dec;

}

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_bitminter WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$data = bitminter_query("https://bitminter.com/api/users", array("Authorization: key=" . $account['api_key']));
if ($data === null) {
	throw new ExternalAPIException("Invalid JSON detected");
} else {
	if (!isset($data['balances'])) {
		throw new ExternalAPIException("No balances found");
	}
	if (!isset($data['hash_rate'])) {
		throw new ExternalAPIException("No hash rate found");
	}

	$btc = isset($data['balances']['BTC']) ? $data['balances']['BTC'] : 0;
	$nmc = isset($data['balances']['NMC']) ? $data['balances']['NMC'] : 0;
	$hashrate = $data['hash_rate'];

	insert_new_balance($job, $account, $exchange, 'btc', $btc);
	insert_new_hashrate($job, $account, $exchange, 'btc', $hashrate /* mhash */);

	insert_new_balance($job, $account, $exchange, 'nmc', $nmc);
	insert_new_hashrate($job, $account, $exchange, 'nmc', $hashrate /* mhash */);

}
