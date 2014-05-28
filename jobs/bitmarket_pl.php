<?php

/**
 * BitMarket.pl balance job.
 */

$exchange = "bitmarket_pl";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_bitmarket_pl WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// from https://www.bitmarket.pl/download/API_20140402.pdf
function bitmarket_pl_query($key, $secret, $method, array $req = array()) {

	$req['method'] = $method;
	$req['tonce'] = time();

	// generate the POST data string
	$post = http_build_query($req, '', '&');
	$sign = hash_hmac("sha512", $post, $secret);

	// generate the extra headers
	$headers = array(
		'API-Key: ' . $key,
		'API-Hash: ' . $sign,
	);

	// our curl handle (initialize if required)
	$ch = crypto_curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BitMarket.pl PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url("https://www.bitmarket.pl/api2/"));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	// fixes Could not get reply: SSL certificate problem, verify that the CA cert is OK. Details: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed
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
$info = bitmarket_pl_query($account['api_key'], $account['api_secret'], "info");

if (isset($info['error'])) {
	if (isset($info['errorMsg'])) {
		throw new ExternalAPIException("API returned error: '" . htmlspecialchars($info['errorMsg']) . "'");
	} else {
		throw new ExternalAPIException("API returned error " . htmlspecialchars($info['error']) . "");
	}
}

$get_supported_wallets = get_supported_wallets();
$currencies = $get_supported_wallets['bitmarket_pl']; // btc, pln, ...

foreach ($currencies as $currency) {
	if (!isset($info['data']['balances']['available'][get_currency_abbr($currency)])) {
		// this shouldn't ever happen
		throw new ExternalAPIException("Did not find any " . get_currency_abbr($currency) . " available balance in response");
	}

	$balance = $info['data']['balances']['available'][get_currency_abbr($currency)] + $info['data']['balances']['blocked'][get_currency_abbr($currency)];
	crypto_log($exchange . " balance for " . $currency . ": " . $balance);
	insert_new_balance($job, $account, $exchange, $currency, $balance);

}
