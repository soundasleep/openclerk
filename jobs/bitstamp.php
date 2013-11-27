<?php

/**
 * Bitstamp balance job.
 */

$exchange = "bitstamp";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_bitstamp WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

function bitstamp_query($key, $client_id, $secret, $url) {

	$nonce = time();
	$message = $nonce . sprintf("%05d", $client_id) . $key;		// the $client_id must be five digits or longer; not specified in API, to prevent "Invalid signature" errors
	$signature = strtoupper(hash_hmac("sha256", $message, $secret));

	// generate the POST data string
	$req = array(
		'key' => $key,
		'signature' => $signature,
		'nonce' => $nonce,
	);
	$post_data = http_build_query($req, '', '&');
	crypto_log($post_data);

	// our curl handle (initialize if required)
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Bitstamp PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($url));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	$dec = json_decode($res, true);
	if (!$dec) throw new ExternalAPIException('Invalid data received, please make sure connection is working and requested API exists');
	return $dec;
}

$balance = bitstamp_query($account['api_key'], $account['api_client_id'], $account['api_secret'], "https://www.bitstamp.net/api/balance/");
crypto_log(print_r($balance, true));

if (isset($balance['error'])) {
	throw new ExternalAPIException(htmlspecialchars($balance['error']));
}

$get_supported_wallets = get_supported_wallets();
$currencies = $get_supported_wallets['bitstamp']; // btc, usd

foreach ($currencies as $currency) {
	if (!isset($balance[$currency . '_balance'])) {
		crypto_log("No $exchange balance for $currency");
		continue;
	}

	// also $currency_reserved and $currency_available; we use $currency_balance
	$b = $balance[$currency . '_balance'];
	crypto_log($exchange . " balance for " . $currency . ": " . $b);

	insert_new_balance($job, $account, $exchange, $currency, $b);
}
