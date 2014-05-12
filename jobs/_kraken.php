<?php

/**
 * Based on https://www.kraken.com/help/api#get-account-balance
 * and https://github.com/payward/kraken-api-client/blob/master/php/KrakenAPIClient.php
 * @param $path e.g. '/0/private/Balance';
 */
function kraken_query($key, $secret, $path, array $request = array()) {

    // generate a 64 bit nonce using a timestamp at microsecond resolution
    // string functions are used to avoid problems on 32 bit systems
    $nonce = explode(' ', microtime());
	$request['nonce'] = $nonce[1] . str_pad(substr($nonce[0], 2, 6), 6, '0');

	// generate the POST data string
	$post_data = http_build_query($request, '', '&');

	// set API key and sign the message
    $sign = hash_hmac('sha512', $path . hash('sha256', $request['nonce'] . $post_data, true), base64_decode($secret), true);

	// generate the extra headers
	$headers = array(
		'Api-Key: ' . $key,
		'Api-Sign: ' . base64_encode($sign),
	);

	// our curl handle (initialize if required)
	$ch = crypto_curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Kraken PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url("https://api.kraken.com" . $path));
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	// fixes Could not get reply: SSL certificate problem, verify that the CA cert is OK. Details: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	$dec = crypto_json_decode($res);
	if (!$dec) {
		crypto_log(htmlspecialchars($res));
		throw new ExternalAPIException('Invalid data received');
	}
	return $dec;
}

/**
 * Map something like 'XXBT' into 'btc', 'ZUSD' into 'usd' - used for
 * Kraken exchange.
 *
 * @return 3-character currency code, or {@code false} if none is found
 * @see #get_iso4_name()
 */
function map_iso4_name($cur) {
	switch (strtolower($cur)) {
		case "xxbt": return "btc";
		case "xltc": return "ltc";
		case "xxrp": return "xrp";
		case "xnmc": return "nmc";
		case "xxdg": return "dog";
		// case "xxvn": return "ven"; - "Ven (XVN) is the only digital currency that's completely back by assets"

		case "zusd": return "usd";
		case "zeur": return "eur";
		case "zgbp": return "gbp";
		case "zkrw": return "krw";
		case "zcad": return "cad";
		case "zcny": return "cny";
		case "zrub": return "rur";
		case "zjpy": return "jpy";
		case "zaud": return "aud";

		default:
			throw new JobException("Unknown iso4 code " . htmlspecialchars($cur));
	}
}

function get_iso4_name($cur) {
	switch ($cur) {
		case "btc": return "xxbt";
		case "ltc": return "xltc";
		case "xrp": return "xxrp";
		case "nmc": return "xnmc";
		case "dog": return "xxdg";
		// case "xxvn": return "ven"; - "Ven (XVN) is the only digital currency that's completely back by assets"

		case "usd": return "zusd";
		case "eur": return "zeur";
		case "gbp": return "zgbp";
		case "krw": return "zkrw";
		case "cad": return "zcad";
		case "cny": return "zcny";
		case "rur": return "zrub";
		case "jpy": return "zjpy";
		case "aud": return "zaud";

		default:
			throw new JobException("Unknown currency for iso4 " . htmlspecialchars($cur));
	}
}
