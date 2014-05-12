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
	$ch = crypto_curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; 796 PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($token_url));
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	$dec = crypto_json_decode($res, "in authentication");

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
	$ch = crypto_curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; 796 PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($destination_url));
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	$dec = crypto_json_decode($res, "in request");

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
	if (!isset($balance['main_wallet'][$currency])) {
		crypto_log("No main_wallet $exchange balance for $currency");
		continue;
	}
        if (!isset($balance['futures_wallet'][$currency])) {
                crypto_log("No futures_wallet $exchange balance for $currency");
                // continue;
		$balance['futures_wallet'][$currency] = 0;
        }

	// also $currency_reserved and $currency_available; we use $currency_balance
	$b = $balance['main_wallet'][$currency] + $balance['futures_wallet'][$currency];
	crypto_log($exchange . " balance for " . $currency . ": " . $b);

	insert_new_balance($job, $account, $exchange . "_wallet", $currency, $b);
}

// set is_recent=0 for all old security instances for this user
$q = db()->prepare("UPDATE securities SET is_recent=0 WHERE user_id=? AND exchange=? AND account_id=?");
$q->execute(array($job['user_id'], $exchange, $account['id']));

// also get all supported securities
$q = db()->prepare("SELECT * FROM securities_796");
$q->execute();
$securities = $q->fetchAll();
$securities_balance = 0;
foreach ($securities as $security_def) {
	if (isset($balance[$security_def['name']])) {
		// get the latest balance
		// the 'balance' for this security is the 'bid'
		$q = db()->prepare("SELECT * FROM balances WHERE exchange=:exchange AND account_id=:account_id AND is_recent=1 LIMIT 1");
		$q->execute(array(
			"exchange" => "securities_796",
			"account_id" => $security_def['id'],
		));
		$security_value = $q->fetch();
		if (!$security_value) {
			// we can't calculate the value of this security yet
			crypto_log("Security " . htmlspecialchars($security_def['name']) . " does not yet have a calculated value");

		} else {

			$calculated = $security_value['balance'] * $balance[$security_def['name']];
			crypto_log(htmlspecialchars($security_def['name']) . " @ " . htmlspecialchars($security_value['balance']) . " x " . number_format($balance[$security_def['name']]) . " = " . htmlspecialchars($calculated));

			$securities_balance += $calculated;

		}

		// insert security instance
		// but only if we actually have a quantity
		if ($balance[$security_def['name']] != 0) {
			$q = db()->prepare("INSERT INTO securities SET user_id=:user_id, exchange=:exchange, security_id=:security_id, quantity=:quantity, account_id=:account_id, is_recent=1");
			$q->execute(array(
				'user_id' => $job['user_id'],
				'exchange' => $exchange,
				'security_id' => $security_def['id'],
				'quantity' => $balance[$security_def['name']],
				'account_id' => $account['id'],
			));
		}

	}
}

// we've now calculated the value of all securities too
insert_new_balance($job, $account, $exchange . "_securities", $currency, $securities_balance);
