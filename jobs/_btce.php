<?php

// from btc-e documentation somewhere
function btce_query($key, $secret, $method, array $req = array()) {

	$req['method'] = $method;
	$mt = explode(' ', microtime());
	$req['nonce'] = $mt[1];

	// generate the POST data string
	$post_data = http_build_query($req, '', '&');

	$sign = hash_hmac("sha512", $post_data, $secret);

	// generate the extra headers
	$headers = array(
			'Sign: '.$sign,
			'Key: '.$key,
	);

	// our curl handle (initialize if required)
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BTCE PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url('https://btc-e.com/tapi/'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	$dec = crypto_json_decode($res);
	return $dec;
}
