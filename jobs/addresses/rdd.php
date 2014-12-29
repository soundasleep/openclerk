<?php

/**
 * Reddcoin (RDD) Search job.
 */

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

$raw = crypto_get_contents(crypto_wrap_url("http://live.reddcoin.com/api/addr/" . urlencode($address['address']) . "/?noTxList=1"));
if (strpos(strtolower($raw), "invalid address") !== false) {
	throw new ExternalAPIException("Invalid address");
}

$data = crypto_json_decode($raw);

if ($address['is_received']) {
	if (isset($data['totalReceived'])) {
		$balance = $data['totalReceived'];
		insert_new_address_balance($job, $address, $balance);
	} else {
		throw new ExternalAPIException("Could not find received balance");
	}
} else {
	if (isset($data['balance'])) {
		$balance = $data['balance'];
		insert_new_address_balance($job, $address, $balance);
	} else {
		throw new ExternalAPIException("Could not find current balance");
	}
}
