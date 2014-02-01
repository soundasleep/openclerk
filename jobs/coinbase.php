<?php

/**
 * Coinbase balance job.
 */

$exchange = "coinbase";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_coinbase WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

function coinbase_query($method, $account) {

	// do we need to get a new access token using our refresh token?
	$need_refresh = ($account['access_token'] && time() > strtotime($account['access_token_expires']));
	$need_access = !$account['access_token'];
	if ($need_access || $need_refresh) {

		// first we want to get the access token, either through code or refresh_token
		$req = array(
			"grant_type" => "authorization_code",
			"redirect_uri" => absolute_url(url_for('coinbase')),
			"client_id" => get_site_config('coinbase_client_id'),
			"client_secret" => get_site_config('coinbase_client_secret'),
		);

		if ($account['refresh_token']) {
			crypto_log("Using refresh token to get access token");
			$req['refresh_token'] = $account['refresh_token'];
		} else {
			crypto_log("Using code to get access token");
			$req['code'] = $account['api_code'];
		}

		// generate the POST data string
		$post_data = http_build_query($req);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Coinbase PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
		curl_setopt($ch, CURLOPT_URL, crypto_wrap_url("https://coinbase.com/oauth/token"));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		// run the query
		$res = curl_exec($ch);
		$response = crypto_json_decode($res);

		crypto_log(print_r($response, true));

		if (isset($response['error_description'])) {
			throw new ExternalAPIException("Coinbase OAuth2 returned " . $response['error_description']);
		}
		if (!isset($response['access_token'])) {
			throw new ExternalAPIException("Coinbase OAuth2 did not return access token");
		}
		if (!isset($response['refresh_token'])) {
			throw new ExternalAPIException("Coinbase OAuth2 did not return refresh token");
		}

		// save the refresh_token
		crypto_log("Saved refresh token '" . $response['refresh_token'] . "'");
		$q = db()->prepare("UPDATE accounts_coinbase SET refresh_token=?,access_token=?,access_token_expires=DATE_ADD(NOW(), INTERVAL ? SECOND) WHERE id=?");
		$q->execute(array($response['refresh_token'], $response['access_token'], $response['expires_in'], $account['id']));
		$account['refresh_token'] = $response['refresh_token'];
		$account['access_token'] = $response['access_token'];

	} else {
		crypto_log("No need to refresh access token; access token expires in " . recent_format_html($account['access_token_expires']));
	}

	// now actually execute the method
	$url = url_add("https://coinbase.com/api/" . $method, array(
		"access_token" => $account['access_token'],
	));

	$res = crypto_get_contents(crypto_wrap_url($url));
	$response2 = crypto_json_decode($res);
	return $response2;

}

$data = coinbase_query("v1/account/balance", $account);
crypto_log(print_r($data, true));

if (!isset($data['amount'])) {
	throw new ExternalAPIException("No balance was returned");
}
if (!isset($data['currency'])) {
	throw new ExternalAPIException("No currency was returned");
}
if ($data['currency'] != 'BTC') {
	throw new ExternalAPIException("Currency was not BTC");
}

$balance = $data['amount'];
$currency = 'btc';
insert_new_balance($job, $account, $exchange, $currency, $balance);
