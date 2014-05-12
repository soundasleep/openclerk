<?php

/**
 * Bitcurex PLN balance job.
 * The API documentation doesn't actually say what to do with key/secret. We use the
 * approach listed on http://stackoverflow.com/questions/13893824/translating-php-to-python-rest-api-connection
 * which looks similar to Mt.Gox
 */

$exchange = "bitcurex_pln";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_bitcurex_pln WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// from mtgox documentation somewhere
function bitcurex_pln_query($key, $secret, $path, array $req = array()) {

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
	$ch = crypto_curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Bitcurex PLN PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($path));
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
$info = bitcurex_pln_query($account['api_key'], $account['api_secret'], 'https://pln.bitcurex.com/api/0/getFunds');
if (isset($info['error']) && $info['error']) {
	throw new ExternalAPIException("API returned error: '" . htmlspecialchars($info['error']) . "'");
}

if (isset($mtgox_info['error'])) {
	throw new ExternalAPIException("API returned error: '" . $mtgox_info['error'] . "'");
}
foreach (array('pln', 'btc') as $currency) {
	if (!isset($info[$currency . "s"])) {
		// this shouldn't ever happen
		throw new ExternalAPIException("Did not find any " . get_currency_abbr($currency) . " balance in response");
	}

	$balance = $info[$currency . "s"];
	crypto_log($exchange . " balance for " . $currency . ": " . $balance);
	insert_new_balance($job, $account, $exchange, $currency, $balance);

}
