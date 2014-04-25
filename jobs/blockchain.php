<?php

/**
 * Blockchain job (BTC).
 */

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

if ($address['is_received']) {
	crypto_log("Need to get received balance rather than current balance for address " . htmlspecialchars($address['address']) . ".");
	$url = "http://blockchain.info/q/getreceivedbyaddress/" . urlencode($address['address']) . "?confirmations=" . get_site_config('btc_confirmations');
} else {
	$url = "http://blockchain.info/q/addressbalance/" . urlencode($address['address']) . "?confirmations=" . get_site_config('btc_confirmations');
}
if (get_site_config('blockchain_api_key')) {
	crypto_log("Using Blockchain API key.");
	$url = url_add($url, array('api_code' => get_site_config('blockchain_api_key')));
}
$balance = crypto_get_contents(crypto_wrap_url($url));
$divisor = 1e8;		// divide by 1e8 to get btc balance

if (!is_numeric($balance)) {
	crypto_log("Blockchain balance for " . htmlspecialchars($address['address']) . " is non-numeric: " . htmlspecialchars($balance));
	if ($balance == "Checksum does not validate") {
		throw new ExternalAPIException("Checksum does not validate");
	}
	throw new ExternalAPIException("Blockchain returned non-numeric balance");
} else {
	crypto_log("Blockchain balance for " . htmlspecialchars($address['address']) . ": " . ($balance / $divisor));
}

insert_new_address_balance($job, $address, $balance / $divisor);

