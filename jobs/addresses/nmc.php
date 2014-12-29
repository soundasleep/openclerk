<?php

/**
 * Namecoin Search job.
 * We use http://namecha.in/ directly (#182).
 * We could extend this in the future to take away unconfirmed transactions, but it's not critical.
 */

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

$html = crypto_get_contents(crypto_wrap_url(sprintf(get_site_config('nmc_address_url'), urlencode($address['address']))));

// look for 'current balance'
if ($address['is_received']) {
	$matches = false;
	if (preg_match("#>Total received</td>.+?<td>([0-9\.]+) NMC</td>#im", $html, $matches)) {
		$balance = $matches[1];
		insert_new_address_balance($job, $address, $balance);
	} else {
		throw new ExternalAPIException("Could not find received balance on page");
	}
} else {
	$matches = false;
	if (preg_match("#>Balance: ([0-9\.]+) NMC#im", $html, $matches)) {
		$balance = $matches[1];
		insert_new_address_balance($job, $address, $balance);
	} else {
		throw new ExternalAPIException("Could not find current balance on page");
	}
}
