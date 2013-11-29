<?php

/**
 * BIPS balance job.
 */

$exchange = "bips";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_bips WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

function bips_query($url, $userpwd, array $req = array()) {

	// our curl handle (initialize if required)
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BIPS PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($url));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_USERPWD, $userpwd);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	if ($res === "Access denied") {
		throw new ExternalAPIException("API response: Access denied");
	}
	$dec = crypto_json_decode($res);
	return $dec;

}

$data = bips_query("https://bips.me/api/v1/getbalance", $account['api_key'], array("currency" => 'USD'));
$currencies = array('btc' => 'btc', 'usd' => 'fiat');
foreach ($currencies as $currency => $key) {
	if (!isset($data[$key]['amount'])) {
		throw new ExternalAPIException("Did not find funds for currency $currency in $exchange");
	}

	$balance = $data[$key]['amount'];
	insert_new_balance($job, $account, $exchange, $currency, $balance);

}
