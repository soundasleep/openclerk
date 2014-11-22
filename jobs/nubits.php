<?php

/**
 * Nubits (NBT) Search job.
 */

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

$raw = crypto_get_contents(crypto_wrap_url("https://blockexplorer.nu/api/addressInfo/" . urlencode($address['address'])));
$data = crypto_json_decode($raw);

if (!$data['exists']) {
	throw new ExternalAPIException("Address does not exist");
}

if ($address['is_received']) {
	if (isset($data['totalInInt'])) {
		$balance = $data['totalInInt'];
		insert_new_address_balance($job, $address, $balance);
	} else {
		throw new ExternalAPIException("Could not find received balance");
	}
} else {
	if (isset($data['totalBalanceInt'])) {
		$balance = $data['totalBalanceInt'];
		insert_new_address_balance($job, $address, $balance);
	} else {
		throw new ExternalAPIException("Could not find current balance");
	}
}
