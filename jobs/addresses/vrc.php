<?php

/**
 * Vericoin Search job.
 * We use cryptoid.info directly, but this does not provide confirmations.
 */

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

if ($address['is_received']) {
	crypto_log("We are looking for received balance.");
	$url = sprintf(get_site_config('vrc_received_url'), urlencode($address['address']));
} else {
	$url = sprintf(get_site_config('vrc_balance_url'), urlencode($address['address']));
}

$balance = crypto_get_contents(crypto_wrap_url($url));
if (!is_numeric($balance)) {
	crypto_log("Balance for " . htmlspecialchars($address['address']) . " is non-numeric: " . htmlspecialchars($balance));
	throw new ExternalAPIException("Explorer returned non-numeric balance");
}
crypto_log("Found balance " . $balance);

insert_new_address_balance($job, $address, $balance);
