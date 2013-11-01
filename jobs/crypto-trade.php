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

	// generate a nonce as microtime, with as-string handling to avoid problems with 32bits systems
	$mt = explode(' ', microtime());

	$req = array(
		'nonce' => $mt[1].substr($mt[0], 2, 6),
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

// we process both wallets...
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

// ...and also securities
// set is_recent=0 for all old security instances for this user
$q = db()->prepare("UPDATE securities SET is_recent=0 WHERE user_id=? AND exchange=?");
$q->execute(array($job['user_id'], $exchange));

$q = db()->prepare("SELECT * FROM securities_cryptotrade");
$q->execute();
$securities = $q->fetchAll();
$security_value = array();
foreach ($securities as $sec) {
	if (!isset($security_value[$sec['currency']])) {
		$security_value[$sec['currency']] = 0;
	}

	// find the last security value
	$q = db()->prepare("SELECT * FROM balances WHERE exchange=? AND account_id=? AND is_recent=1 LIMIT 1");
	$q->execute(array("securities_crypto-trade", $sec['id']));
	if ($balance = $q->fetch()) {
		$currency = strtolower($sec['name']);
		if (!isset($info['data']['funds'][$currency])) {
			// no security value found
			crypto_log("Did not find funds for currency $currency in $exchange");

		} else {
			// calculate the security value
			if ($info['data']['funds'][$currency] >= 0) {
				$temp = $info['data']['funds'][$currency] * $balance['balance'];
				crypto_log($info['data']['funds'][$currency] . " x " . $balance['balance'] . " = " . $balance . " " . $sec['currency']);
				$security_value[$sec['currency']] += $temp;

				// insert security instance
				$q = db()->prepare("INSERT INTO securities SET user_id=:user_id, exchange=:exchange, security_id=:security_id, quantity=:quantity, is_recent=1");
				$q->execute(array(
					'user_id' => $job['user_id'],
					'exchange' => $exchange,
					'security_id' => $sec['id'],
					'quantity' => $info['data']['funds'][$currency],
				));
			}
		}

	} else {
		crypto_log("Could not find any recent balance for " . $sec['name']);
	}
}

crypto_log("Securities values: " . print_r($security_value, true));
foreach ($security_value as $currency => $value) {
	insert_new_balance($job, $account, $exchange . '_securities', $currency, $value);
}

