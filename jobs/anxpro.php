<?php

/**
 * ANXPRO balance job.
 */

$exchange = "anxpro";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_anxpro WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// based on http://docs.anxv2.apiary.io/
function anxpro_query($key, $secret, $path, array $req = array()) {
	$root = "https://anxpro.com/api/2/";

	// generate a nonce as microtime, with as-string handling to avoid problems with 32bits systems
	$mt = explode(' ', microtime());
	$req['nonce'] = $mt[1].substr($mt[0], 2, 6);

	// generate the POST data string
	$post_data = http_build_query($req, '', '&');

	// generate the extra headers
	$headers = array(
		'Rest-Key: ' . $key,
		'Rest-Sign: ' . base64_encode(hash_hmac('sha512', $path . "\0" . $post_data, base64_decode($secret), true)),
		'Content-Type: application/x-www-form-urlencoded',
	);

	// our curl handle (initialize if required)
	$ch = crypto_curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; ANXPRO PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($root . $path));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	// fixes Could not get reply: SSL certificate problem, verify that the CA cert is OK. Details: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	if (!$res) {
		// this also happens if the sign was calculated incorrectly
		throw new ExternalAPIException("Empty response received");
	}
	$dec = json_decode($res, true);
	if (!$dec) {
		crypto_log(htmlspecialchars($res));
		throw new ExternalAPIException('Invalid data received');
	}
	return $dec;
}

// $info = anxpro_query($account['api_key'], $account['api_secret'], 'https://anxpro.com/api/2/money/info');
$info = anxpro_query($account['api_key'], $account['api_secret'], 'money/info');
if (isset($info['error'])) {
	throw new ExternalAPIException("API returned error: '" . $info['error'] . "'");
}

$wallets = get_supported_wallets();
foreach ($wallets['anxpro'] as $currency) {
	if (!isset($info['data']['Wallets'][get_currency_abbr($currency)])) {
		throw new ExternalAPIException("Did not find any wallet for " . get_currency_abbr($currency));
	}
	if (!isset($info['data']['Wallets'][get_currency_abbr($currency)]['Balance']['value'])) {
		throw new ExternalAPIException("Did not find any balance for " . get_currency_abbr($currency));
	}

	// also available: Available_Balance, Daily_Withdrawl_Limit, Max_Withdraw
	$balance = $info['data']['Wallets'][get_currency_abbr($currency)]['Balance']['value'];

	insert_new_balance($job, $account, $exchange, $currency, $balance);

}
