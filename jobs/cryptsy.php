<?php

/**
 * Cryptsy balance job.
 */

$exchange = "cryptsy";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_cryptsy WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// from cryptsy API documentation https://www.cryptsy.com/pages/api
function cryptsy_query($key, $secret, $method, array $req = array()) {

	$req['method'] = $method;
	$mt = explode(' ', microtime());
	$req['nonce'] = $mt[1];

	// generate the POST data string
	$post_data = http_build_query($req, '', '&');

	// generate the extra headers
	$headers = array(
		'Sign: '.hash_hmac('sha512', $post_data, $secret),
		'Key: '.$key,
	);

	// our curl handle (initialize if required)
	$ch = crypto_curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Cryptsy PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url("https://www.cryptsy.com/api"));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	$dec = crypto_json_decode($res, true);
	return $dec;
}

$get_supported_wallets = get_supported_wallets();
$currencies1 = $get_supported_wallets['cryptsy'];

$cryptsy_info = cryptsy_query($account['api_public_key'], $account['api_private_key'], "getinfo");

if (isset($cryptsy_info['error']) && $cryptsy_info['error']) {
	throw new ExternalAPIException("API returned error: '" . htmlspecialchars($cryptsy_info['error']) . "'");
}

foreach ($currencies1 as $cur) {
	if (!isset($cryptsy_info['return']['balances_available'][get_currency_abbr($cur)])) {
		crypto_log("Did not find any " . get_currency_abbr($cur) . " currency in $exchange");
		continue;
	}

	// also available: 'balances_hold'
	$balance = $cryptsy_info['return']['balances_available'][get_currency_abbr($cur)];
	crypto_log($exchange . " balance for " . $cur . ": " . $balance);
	insert_new_balance($job, $account, $exchange, $cur, $balance);

}
