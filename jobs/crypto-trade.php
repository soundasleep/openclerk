<?php

/**
 * Crypto-Trade balance job.
 */

$exchange = "crypto-trade";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_cryptotrade WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

function cryptotrade_query($key, $secret, $url) {

	$req = array(
		'nonce' => time(),
	);

	// generate the POST data string
	$post_data = http_build_query($req, '', '&');

	$sign = hash_hmac("sha512", $post_data, $secret);

	// generate the extra headers
	$headers = array(
		'AuthKey: '.$key,
		'AuthSign: '.$sign,
	);

	// our curl handle (initialize if required)
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Crypto-Trade PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($url));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	$dec = json_decode($res, true);
	if (!$dec) throw new ExternalAPIException('Invalid data received, please make sure connection is working and requested API exists');
	return $dec;

}

$info = cryptotrade_query($account['api_key'], $account['api_secret'], 'https://crypto-trade.com/api/1/private/getinfo');
if (isset($info['error'])) {
	throw new ExternalAPIException(htmlspecialchars($info['error']));
}
crypto_log(print_r($info, true));

$get_supported_wallets = get_supported_wallets();
$currencies = $get_supported_wallets['crypto-trade']; // also supports trc, cnc, wdc etc
foreach ($currencies as $currency) {

	crypto_log($exchange . " balance for " . $currency . ": " . $info['data']['funds'][$currency]);
	if (!isset($info['data']['funds'][$currency])) {
		throw new ExternalAPIException("Did not find funds for currency $currency in $exchange");
	}

	$balance = $info['data']['funds'][$currency];
	insert_new_balance($job, $account, $exchange, $currency, $balance);

}
