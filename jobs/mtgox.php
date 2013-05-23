<?php

/**
 * Mt.Gox balance job.
 */

$exchange = "mtgox";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_mtgox WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// from mtgox documentation somewhere
function mtgox_query($key, $secret, $path, array $req = array()) {

	// generate a nonce as microtime, with as-string handling to avoid problems with 32bits systems
	$mt = explode(' ', microtime());
	$req['nonce'] = $mt[1].substr($mt[0], 2, 6);

	// generate the POST data string
	$post_data = http_build_query($req, '', '&');

	// generate the extra headers
	$headers = array(
		'Rest-Key: '.$key,
		'Rest-Sign: '.base64_encode(hash_hmac('sha512', $post_data, base64_decode($secret), true)),
	);

	// our curl handle (initialize if required)
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MtGox PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($path));
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

$currencies = array('btc' => 1e8, 'usd' => 1e5, 'eur' => 1e5 /* assumed */);
$mtgox_info = mtgox_query($account['api_key'], $account['api_secret'], 'https://mtgox.com/api/1/generic/private/info');
if (isset($mtgox_info['error'])) {
	throw new ExternalAPIException("API returned error: '" . $mtgox_info['error'] . "'");
}
foreach ($currencies as $currency => $divisor) {
	if (!isset($mtgox_info['return']['Wallets'][strtoupper($currency)])) {
		// e.g. this is an AUD/BTC wallet; we shouldn't fail outright
		crypto_log("Did not find any " . strtoupper($currency) . " currency in $exchange");
		continue;
	}

	crypto_log($exchange . " balance for " . $currency . ": " . ($mtgox_info['return']['Wallets'][strtoupper($currency)]['Balance']['value_int'] / $divisor));
	if (!isset($mtgox_info['return']['Wallets'][strtoupper($currency)]['Balance']['value_int'])) {
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
	$q = db()->prepare("INSERT INTO balances SET user_id=:user_id, exchange=:exchange, account_id=:account_id, balance=:balance / :divisor, currency=:currency, is_recent=1");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
		"balance" => $mtgox_info['return']['Wallets'][strtoupper($currency)]['Balance']['value_int'],
		"divisor" => $divisor,
	));
	crypto_log("Inserted new $exchange $currency balances id=" . db()->lastInsertId());

}
