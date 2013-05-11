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
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BTCE PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
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
	$dec = json_decode($res, true);
	if (!$dec) throw new ExternalAPIException('Invalid data received, please make sure connection is working and requested API exists');
	return $dec;
}

$data = bips_query("https://bips.me/api/v1/getbalance", $account['api_key'], array("currency" => 'USD'));
$currencies = array('btc' => 'btc', 'usd' => 'fiat');
foreach ($currencies as $currency => $key) {
	crypto_log($exchange . " balance for " . $currency . ": " . $data[$key]['amount']);
	if (!isset($data[$key]['amount'])) {
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
		"balance" => $data[$key]['amount'],
		// we ignore server_time
	));
	crypto_log("Inserted new $exchange $currency balances id=" . db()->lastInsertId());

}
