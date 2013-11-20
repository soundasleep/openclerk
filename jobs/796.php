<?php

/**
 * 796 balance job.
 */

$exchange = "796";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_796 WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

function xchange796_query($appid, $apikey, $secretkey, $url) {

	// from https://796.com/wiki.html
	$timestamp = time();
	$params	= array(
	    'appid' => $appid,
	    'apikey' => $apikey,
	    'secretkey' => $secretkey,
	    'timestamp' => $timestamp,
	);
	ksort($params);	// "be careful that the sequence is quite important"
	$param_uri = http_build_query($params,'','&');
	$sig = base64_encode(hash_hmac('sha1', $param_uri, $secretkey));

	$token_url = url_add("https://796.com/oauth/token", array(
		'appid' => $appid,
		'timestamp' => $timestamp,
		'apikey' => $apikey,
		'secretkey' => $secretkey,	// I don't know why the secretkey is included - it should be secret
		'sig' => $sig,
	));

	// our curl handle (initialize if required)
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; 796 PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($token_url));
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	$dec = json_decode($res, true);
	if (!$dec) throw new ExternalAPIException('Invalid data received, please make sure connection is working and requested API exists');

	if (isset($dec['errno']) && $dec['errno']) {
		throw new ExternalAPIException("Could not get OAuth Token: " . htmlspecialchars($dec['msg']));
	}

	if (!isset($dec['data']['access_token'])) {
		throw new ExternalAPIException("No access token provided");
	}
	$token = $dec['data']['access_token'];
	crypto_log("Obtained OAuth token");

	// now, call the given URL
	// 796 has a bug where the token can't be urlencoded again, so we can't use url_add() (though we should)
	$destination_url = $url . "?access_token=" . $token;

	// our curl handle (initialize if required)
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; 796 PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($destination_url));
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	$dec = json_decode($res, true);
	if (!$dec) throw new ExternalAPIException('Invalid data received, please make sure connection is working and requested API exists');

	if (isset($dec['errno']) && $dec['errno']) {
		throw new ExternalAPIException("Error in reply: " . htmlspecialchars($dec['msg']));
	}
	if (!isset($dec['data'])) {
		throw new ExternalAPIException("No data in reply");
	}

	return $dec['data'];

}

// $balance = bitstamp_query($account['api_key'], $account['api_client_id'], $account['api_secret'], "https://www.bitstamp.net/api/balance/");
$balance = xchange796_query($account['api_app_id'], $account['api_key'], $account['api_secret'], 'https://796.com/v1/user/get_balance');
crypto_log(print_r($balance, true));

$get_supported_wallets = get_supported_wallets();
$currencies = $get_supported_wallets['796']; // btc, usd

foreach ($currencies as $currency) {
	if (!isset($balance[$currency])) {
		crypto_log("No $exchange balance for $currency");
		continue;
	}

	// also $currency_reserved and $currency_available; we use $currency_balance
	$b = $balance[$currency];
	crypto_log($exchange . " balance for " . $currency . ": " . $b);

	insert_new_balance($job, $account, $exchange, $currency, $b);
}

// TODO implement securities
