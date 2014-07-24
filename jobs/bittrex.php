<?php

/**
 * Bittrex balance job.
 */

$exchange = "bittrex";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_bittrex WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// based on https://bittrex.com/Home/Api
function bittrex_query($key, $secret, $path, array $req = array()) {

	$nonce = time();
	$path = url_add($path, array('apikey' => $key, 'nonce' => $nonce));
	$sign = hash_hmac('sha512', $path, $secret);

	// generate the extra headers
	$headers = array(
		'apisign: ' . $sign,
	);

	// our curl handle (initialize if required)
	$ch = crypto_curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Bittrex PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($path));
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

// returns eurs, btcs, address
$info = bittrex_query($account['api_key'], $account['api_secret'], 'https://bittrex.com/api/v1/account/getbalances');
crypto_log(print_r($info, true));

if (!$info['success']) {
	if (isset($info['message'])) {
		throw new ExternalAPIException(htmlspecialchars($info['message']));
	} else {
		throw new ExternalAPIException("API failed with no error message");
	}
}

$get_supported_wallets = get_supported_wallets();
$currencies = $get_supported_wallets['bittrex'];
foreach ($info['result'] as $row) {
	// go through the currencies we're expecting
	foreach ($currencies as $currency) {
		if ($row['Currency'] == get_currency_abbr($currency)) {
			if (!isset($row['Balance'])) {
				throw new ExternalAPIException("Did not find any " . get_currency_abbr($currency) . " balance in response");
			}

			$balance = $row['Balance'];
			crypto_log($exchange . " balance for " . $currency . ": " . $balance);
			insert_new_balance($job, $account, $exchange, $currency, $balance);

		}
	}
}
