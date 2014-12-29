<?php

/**
 * Blackcoin Search job.
 * We use Blackchain directly, but this does not provide confirmations.
 */

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

$result = crypto_json_decode(crypto_get_contents(crypto_wrap_url(get_site_config('bc1_balance_url') . urlencode($address['address']) . "?noTxList=1")));

if ($address['is_received']) {
	crypto_log("We are looking for received balance.");

	$balance = $result['totalReceivedSat'];
	$divisor = 1e8;
} else {
	$balance = $result['balanceSat'];
	$divisor = 1e8;
	// also available: totalSentSat, unconfirmedBalanceSat, taxAppearances, ...
}
crypto_log("Found balance " . ($balance / $divisor));

insert_new_address_balance($job, $address, $balance / $divisor);
