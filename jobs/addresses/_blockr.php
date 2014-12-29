<?php

/**
 * Obtains address data from the awesome Blockr.io API.
 * Expects $currency and $confirmations input.
 * Supports confirmations and supports is_received.
 */

if (!isset($currency) || !$currency) {
	throw new JobException("No currency provided");
}
if (!isset($confirmations) || !$confirmations) {
	throw new JobException("No confirmations provided");
}

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

// get the JSON

if ($address['is_received']) {
	throw new ExternalAPIException("is_received is not supported for " . $currency);
}

$url = sprintf(get_site_config($currency . '_address_url'), $address['address']) . "?confirmations=" . $confirmations;

$json = crypto_json_decode(crypto_get_contents(crypto_wrap_url($url)));
$data = crypto_jsend($json);

if ($address['is_received']) {
	if (!isset($data['totalreceived'])) {
		throw new ExternalAPIException("No totalreceived found");
	}
	$balance = $data['totalreceived'];
} else {
	if (!isset($data['balance'])) {
		throw new ExternalAPIException("No balance found");
	}
	$balance = $data['balance'];
}

crypto_log("Blockchain balance for " . htmlspecialchars($address['address']) . ": " . $balance);

if (isset($address['is_valid']) && !$address['is_valid']) {
	throw new ExternalAPIException("Address is not valid");
}

insert_new_address_balance($job, $address, $balance);
