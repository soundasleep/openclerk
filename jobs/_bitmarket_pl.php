<?php

// from https://www.bitmarket.pl/download/API_20140402.pdf
function bitmarket_pl_query($key, $secret, $method, array $req = array()) {

	$req['method'] = $method;
	$req['tonce'] = time();

	// generate the POST data string
	$post = http_build_query($req, '', '&');
	$sign = hash_hmac("sha512", $post, $secret);

	// generate the extra headers
	$headers = array(
		'API-Key: ' . $key,
		'API-Hash: ' . $sign,
	);

	// our curl handle (initialize if required)
	$ch = crypto_curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BitMarket.pl PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url("https://www.bitmarket.pl/api2/"));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
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
