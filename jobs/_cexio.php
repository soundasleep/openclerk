<?php

function cexio_query($key, $username, $secret, $url) {

	$nonce = time();
	$message = $nonce . $username . $key;
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
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; CEX.io PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($url));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	$dec = crypto_json_decode($res);
	return $dec;
}
