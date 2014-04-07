<?php

/**
 * Vertcoin Search job.
 * We use the vertexplorer.com directly, but this does not provide confirmations.
 */

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

$result = crypto_json_decode(crypto_get_contents(crypto_wrap_url(get_site_config('vtc_balance_url') . urlencode($address['address']))));

if ($address['is_received']) {
	crypto_log("We are looking for received balance.");

	$balance = $result['totalReceived'];
} else {
	$balance = $result['balance'];
}
crypto_log("Found balance " . $balance);

// disable old instances
$q = db()->prepare("UPDATE address_balances SET is_recent=0 WHERE is_recent=1 AND user_id=:user_id AND address_id=:address_id");
$q->execute(array(
	"user_id" => $job['user_id'],
	"address_id" => $address['id'],
));

// we have a balance; update the database
$q = db()->prepare("INSERT INTO address_balances SET user_id=:user_id, address_id=:address_id, balance=:balance, is_recent=1");
$q->execute(array(
	"user_id" => $job['user_id'],
	"address_id" => $address['id'],
	"balance" => $balance,
));
crypto_log("Inserted new vertcoin address_balances id=" . db()->lastInsertId());
