<?php

/**
 * Primecoin (XPM) Search job.
 * Using HTML scraping because there doesn't seem to be any API :(
 * In the future, it would be great if we could convert RS addresses (e.g. NXT-...) to account IDs.
 * We could extend this in the future to take away unconfirmed transactions, but it's not critical.
 */

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

$html = crypto_get_contents(crypto_wrap_url(get_site_config('xpm_address_url') . urlencode($address['address'])));

// look for 'current balance'
// strip out HTML
$html = preg_replace("#<[^>]+>#", "", $html);
$html = preg_replace("#[\n\r\t]+#", " ", $html);
$matches = false;
if ($address['is_received']) {
	if (preg_match("#Total received: ([0-9,\.]+) +XPM#im", $html, $matches)) {
		$balance = str_replace(",", "", $matches[1]);
		insert_new_address_balance($job, $address, $balance);
	} else {
		throw new ExternalAPIException("Could not find received balance on page");
	}
} else {
	if (preg_match("#Final balance: ([0-9,\.]+) +XPM#im", $html, $matches)) {
		$balance = str_replace(",", "", $matches[1]);
		insert_new_address_balance($job, $address, $balance);
	} else {
		throw new ExternalAPIException("Could not find current balance on page");
	}
}
