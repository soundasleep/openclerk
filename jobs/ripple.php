<?php

/**
 * Ripple job (XRP).
 */

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

if ($address['is_received']) {
	throw new JobException("Ripple cannot support getting received addresses");
}

$url = "http://s_west.ripple.com:51234/";
$input = array(
	"method" => "account_info",
	"params" => array(
		array(
			"account" => $address['address'],
			"strict" => true,
		),
	),
);

function ripple_query($path, $input) {

	$post_data = json_encode($input);

	// our curl handle (initialize if required)
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Ripple PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_URL, crypto_wrap_url($path));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));
	if (!json_decode($res, true)) {
		throw new ExternalAPIException($res);
	}
	return crypto_json_decode($res);
}

$info = ripple_query($url, $input);
crypto_log(print_r($info, true));

if (!isset($info['result'])) {
	throw new ExternalAPIException("No result found");
}

if (isset($info['result']['error_message']) && $info['result']['error_message']) {
	throw new ExternalAPIException("Ripple returned " . htmlspecialchars($info['result']['error_message']));
}

if (!isset($info['result']['account_data']['Balance'])) {
	throw new ExternalAPIException("No balance found");
}

$balance = $info['result']['account_data']['Balance'];
$divisor = 1e6;		// divide by 1e8 to get xrp balance

crypto_log("Ripple balance for " . htmlspecialchars($address['address']) . ": " . ($balance / $divisor));

// disable old instances
$q = db()->prepare("UPDATE address_balances SET is_recent=0 WHERE is_recent=1 AND user_id=:user_id AND address_id=:address_id");
$q->execute(array(
	"user_id" => $job['user_id'],
	"address_id" => $address['id'],
));

// we have a balance; update the database
$q = db()->prepare("INSERT INTO address_balances SET user_id=:user_id, address_id=:address_id, balance=:balance / :divisor, is_recent=1");
$q->execute(array(
	"user_id" => $job['user_id'],
	"address_id" => $address['id'],
	"balance" => $balance,
	"divisor" => $divisor,
));
crypto_log("Inserted new address_balances id=" . db()->lastInsertId());
