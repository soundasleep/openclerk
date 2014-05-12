<?php

/**
 * Bit2c balance job.
 */

$exchange = "bit2c";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_bit2c WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// from mtgox documentation somewhere
function bit2c_query($key, $secret, $path, array $req = array()) {

	//// generate a nonce as microtime, with as-string handling to avoid problems with 32bits systems
	//$mt = explode(' ', microtime());
	//$req['nonce'] = $mt[1].substr($mt[0], 2, 6);
	$req['nonce'] = time();

	// generate the POST data string
	$post_data = http_build_query($req, '', '&');

	// generate the extra headers
	$headers = array(
		'Content-Type: application/x-www-form-urlencoded',
		'Key: '.$key,
		'Sign: '.base64_encode(hash_hmac('sha512', $post_data, strtoupper($secret), true)),
	);

	// our curl handle (initialize if required)
	$ch = crypto_curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Bit2c PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($path));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	$dec = json_decode($res, true);
	if (!$dec) {
		crypto_log(htmlspecialchars($res));
		throw new ExternalAPIException('Invalid data received');
	}
	return $dec;
}

$get_supported_wallets = get_supported_wallets();
$currencies1 = $get_supported_wallets['bit2c']; // also supports ils
$currencies = array();
foreach ($currencies1 as $cur) {
	$currencies[$cur] = ($cur == 'btc' ? 1e8 : 1e5 /* assumed */);
}
$bit2c_info = bit2c_query($account['api_key'], $account['api_secret'], 'https://www.bit2c.co.il/Account/Balance');
if (isset($bit2c_info['error'])) {
	throw new ExternalAPIException("API returned error: '" . $bit2c_info['error'] . "'");
}

if (!isset($bit2c_info['BalanceBTC'])) {
	crypto_log("No $exchange balance for 'BTC'");
	continue;
}
else {
	$b = $bit2c_info['BalanceBTC'];
	crypto_log($exchange . " balance for btc: " . $b);
	insert_new_balance($job, $account, $exchange, 'btc', $b);
}

if (!isset($bit2c_info['BalanceLTC'])) {
	crypto_log("No $exchange balance for 'LTC'");
	continue;
}
else {
	$b = $bit2c_info['BalanceLTC'];
	crypto_log($exchange . " balance for ltc: " . $b);
	insert_new_balance($job, $account, $exchange, 'ltc', $b);
}

if (!isset($bit2c_info['BalanceNIS'])) {
	crypto_log("No $exchange balance for 'ILS'");
	continue;
}
else {
	$b = $bit2c_info['BalanceNIS'];
	crypto_log($exchange . " balance for ils: " . $b);
	insert_new_balance($job, $account, $exchange, 'ils', $b);
}
