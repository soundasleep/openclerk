<?php

/**
 * BitNZ balance job.
 */

$exchange = "bitnz";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_bitnz WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

function bitnz_query($key, $username, $secret, $url) {

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
	$ch = crypto_curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BitNZ PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($url));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	$dec = crypto_json_decode($res);
	return $dec;
}

$balance = bitnz_query($account['api_key'], $account['api_username'], $account['api_secret'], "https://bitnz.com/api/0/private/balance");
crypto_log(print_r($balance, true));

if (isset($balance['result']) && !$balance['result'] && isset($balance['message'])) {
	throw new ExternalAPIException($balance['message']);
}

$get_supported_wallets = get_supported_wallets();
$currencies = $get_supported_wallets['bitnz']; // ghs, btc

foreach ($currencies as $currency) {
	if (!isset($balance[$currency . '_balance'])) {
		crypto_log("No $exchange balance for $currency");
		continue;
	}

	$b = $balance[$currency . '_balance'];
	crypto_log($exchange . " balance for " . $currency . ": " . $b);

	insert_new_balance($job, $account, $exchange, $currency, $b);
}
