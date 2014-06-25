<?php

/**
 * Ozcoin LTC pool balance job.
 */

$exchange = "ozcoin_ltc";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_ozcoin_ltc WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// strip out any invalid HTML
// "Headers and client library minor version mismatch. Headers:50535 Library:100011"
$contents = crypto_get_contents(crypto_wrap_url("https://lc.ozcoin.net/api.php?api_key=" . $account['api_key']));
$contents = preg_replace("#<br />.+<br />#ims", "", $contents);

$data = crypto_json_decode($contents);

if (isset($data['error'])) {
	throw new ExternalAPIException($data['error']);
}

if (!isset($data['user']['pending_payout'])) {
	throw new ExternalAPIException("No pending payout found");
}
if (!is_numeric($data['user']['pending_payout'])) {
	throw new ExternalAPIException("Pending payout is not numeric");
}
if (!isset($data['user']['hashrate_raw'])) {
	throw new ExternalAPIException("No hashrate found");
}

$currency = 'ltc';
insert_new_balance($job, $account, $exchange, $currency, $data['user']['pending_payout']);
insert_new_hashrate($job, $account, $exchange, $currency, $data['user']['hashrate_raw'] / 1000 /* API returns KHash */);
