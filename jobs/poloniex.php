<?php

/**
 * Poloniex balance job.
 * Based on https://www.poloniex.com/api and http://pastebin.com/iuezwGRZ
 */

$exchange = "poloniex";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_poloniex WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

function poloniex_query($key, $secret, $method, array $req = array()) {

	$req['command'] = $method;

	// generate a nonce as microtime, with as-string handling to avoid problems with 32bits systems
	$mt = explode(' ', microtime());
	$req['nonce'] = $mt[1].substr($mt[0], 2, 6);

	// generate the POST data string
	$post_data = http_build_query($req, '', '&');

	// generate the extra headers
	$headers = array(
		'Key: ' . $key,
		'Sign: ' . hash_hmac('sha512', $post_data, $secret),
	);

	// our curl handle (initialize if required)
	$ch = crypto_curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Poloniex PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url("https://poloniex.com/tradingApi"));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	$dec = json_decode($res, true);
	if (!$dec) {
		crypto_log(htmlspecialchars($res));
		throw new ExternalAPIException('Invalid data received');
	}
	return $dec;
}

// returns plns, btcs, address
$info = poloniex_query($account['api_key'], $account['api_secret'], 'returnBalances');

if (isset($info['error']) && $info['error']) {
	throw new ExternalAPIException("API returned error: '" . htmlspecialchars($info['error']) . "'");
}

$get_supported_wallets = get_supported_wallets();
$currencies = $get_supported_wallets['poloniex']; // btc, ltc, ...

foreach ($currencies as $currency) {
	if (!isset($info[get_currency_abbr($currency)])) {
		// this shouldn't ever happen
		throw new ExternalAPIException("Did not find any " . get_currency_abbr($currency) . " balance in response");
	}

	$balance = $info[get_currency_abbr($currency)];
	crypto_log($exchange . " balance for " . $currency . ": " . $balance);
	insert_new_balance($job, $account, $exchange, $currency, $balance);

}
