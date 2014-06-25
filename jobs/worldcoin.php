<?php

/**
 * Get Worldcoin balance (WDC).
 * Using http://www.worldcoinexplorer.com (Issue #238)
 */

$currency = "wdc";

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

if ($address['is_received']) {
	throw new JobException("is_received is not implemented for $currency");
}
$url = get_site_config($currency . '_address_url') . $address['address'];

$json = crypto_json_decode(crypto_get_contents(crypto_wrap_url($url)));

if (!isset($json['Balance'])) {
	throw new ExternalAPIException("No balance found");
}
$balance = $json['Balance'];

crypto_log("Address balance: " . $balance);

// this API does not support confirmations, so we can't process min_confirmations at all
insert_new_address_balance($job, $address, $balance);
