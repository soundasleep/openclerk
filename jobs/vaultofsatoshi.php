<?php

/**
 * Vault of Satoshi balance job.
 */

$exchange = "vaultofsatoshi";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_vaultofsatoshi WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

/**
 * Based on https://www.vaultofsatoshi.com/api
 * @param $endpoint e.g. '/info/order_detail';
 */
function vaultofsatoshi_query($key, $secret, $endpoint, array $req = array()) {

	// generate a nonce as microtime, with as-string handling to avoid problems with 32bits systems
	$mt = explode(' ', microtime());
	$req['nonce'] = $mt[1].substr($mt[0], 2, 6);

	// generate the POST data string
	$post_data = http_build_query($req, '', '&');

	// generate the extra headers
	$headers = array(
		'Api-Key: '.$key,
		'Api-Sign: '.base64_encode(hash_hmac('sha512', $endpoint . chr(0) . $post_data, $secret)),
	);

	// our curl handle (initialize if required)
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Bitcurex EUR PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url("https://api.vaultofsatoshi.com" . $endpoint));
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	// fixes Could not get reply: SSL certificate problem, verify that the CA cert is OK. Details: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed
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

$balance = vaultofsatoshi_query($account['api_key'], $account['api_secret'], "/info/balance");
if (isset($balance['message']) && $balance['message']) {
	throw new ExternalAPIException(htmlspecialchars($balance['message']));
}

$get_supported_wallets = get_supported_wallets();
$currencies = $get_supported_wallets['vaultofsatoshi']; // btc, usd, ...
foreach ($currencies as $currency) {
	if (!isset($balance['data'][get_currency_abbr($currency)]['value'])) {
		crypto_log("No $exchange balance for $currency");
		continue;
	}

	// we could either use 'value_int' / 10^'precision', but we are storing all
	// values as decimals and 'value' is a string representation - so we can
	// probably just use 'value' directly.
	// see https://www.vaultofsatoshi.com/api#currency_object
	$b = $balance['data'][get_currency_abbr($currency)]['value'];
	insert_new_balance($job, $account, $exchange, $currency, $b);

}
