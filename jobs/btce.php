<?php

/**
 * BTC-e balance job.
 */

$exchange = "btce";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_btce WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

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
	$dec = json_decode($res, true);
	if (!$dec) throw new ExternalAPIException('Invalid data received, please make sure connection is working and requested API exists');
	return $dec;
}

$currencies = array('usd', 'btc', 'ltc', 'nmc');
$btce_info = btce_query($account['api_key'], $account['api_secret'], "getInfo");
if (isset($btce_info['error'])) {
	throw new ExternalAPIException("API returned error: '" . $btce_info['error'] . "'");
}
foreach ($currencies as $currency) {
	crypto_log($exchange . " balance for " . $currency . ": " . $btce_info['return']['funds'][$currency]);
	if (!isset($btce_info['return']['funds'][$currency])) {
		throw new ExternalAPIException("Did not find funds for currency $currency in $exchange");
	}

	// disable old instances
	$q = db()->prepare("UPDATE balances SET is_recent=0 WHERE is_recent=1 AND user_id=:user_id AND exchange=:exchange AND currency=:currency AND account_id=:account_id");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
	));

	// we have a balance; update the database
	$q = db()->prepare("INSERT INTO balances SET user_id=:user_id, exchange=:exchange, account_id=:account_id, balance=:balance, currency=:currency, is_recent=1");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
		"balance" => $btce_info['return']['funds'][$currency],
		// we ignore server_time
	));
	crypto_log("Inserted new $exchange $currency balances id=" . db()->lastInsertId());

}
