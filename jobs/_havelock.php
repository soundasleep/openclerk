<?php

function havelock_query($url, array $req = array()) {

	// our curl handle (initialize if required)
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Havelock PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($url));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	if ($res === "Access denied") {
		throw new ExternalAPIException("API response: Access denied");
	}
	$dec = crypto_json_decode($res);
	if (isset($dec['message'])) {
		throw new ExternalAPIException(htmlspecialchars($dec['message']));
	}
	return $dec;

}