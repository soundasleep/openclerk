<?php

/**
 * Novacoin Search job.
 * We use https://explorer.novaco.in/ directly.
 * We could extend this in the future to take away unconfirmed transactions, but it's not critical.
 */

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

if ($address['is_received']) {
	throw new JobException("We cannot get received balance for Novacoin.");
}

$html = crypto_get_contents(crypto_wrap_url(get_site_config('nvc_address_url') . urlencode($address['address'])));

// look for 'current balance'
$matches = false;
if (preg_match("#Current balance:?</td><td>([0-9\.]+)</td>#im", $html, $matches)) {
	$balance = $matches[1];
	insert_new_address_balance($job, $address, $balance);
} else {
	throw new ExternalAPIException("Could not find current balance on page");
}
