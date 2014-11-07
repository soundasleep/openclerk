<?php

/**
 * BTClevels exchange job.
 */

$exchange = "btclevels";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_btclevels WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

function btclevels_query($url, $key, $secret) {

	$req['key'] = $key;
	$req['secret'] = $secret;

	// our curl handle (initialize if required)
	$ch = crypto_curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BTClevels PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($url));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	if ($res === "Access denied") {
		throw new ExternalAPIException("API response: Access denied");
	}
	$temp = json_decode($res, true);
	if (is_array($temp) && !$temp) {
		throw new ExternalAPIException("API returned an empty array");
	}
	$dec = crypto_json_decode($res);
	if (isset($dec['message'])) {
		throw new ExternalAPIException(htmlspecialchars($dec['message']));
	}
	return $dec;

}

$data = btclevels_query("http://btclevels.com/api/info", $account['api_key'], $account['api_secret']);

if (isset($data['error']) && $data['error']) {
	throw new ExternalAPIException($data['error']);
}

if (!isset($data['info']['balance'])) {
	throw new ExternalAPIException("No balance found");
}

$balance = $data['info']['balance'];
$currency = 'btc';

insert_new_balance($job, $account, $exchange, $currency, $balance);
