<?php

/**
 * Eligius pool balance job.
 */

$exchange = "eligius";
$currency = 'ltc';

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_eligius WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// get hashrate
// (balance is calculated in an overall job)
$data = crypto_json_decode(crypto_get_contents(crypto_wrap_url("http://eligius.st/~wizkid057/newstats/hashrate-json.php/" . $account['btc_address'])));

if (isset($data['error'])) {
	throw new ExternalAPIException($data['error']);
}

// otherwise, lets use the 256 seconds balance
if (!isset($data['256']['hashrate'])) {
	throw new ExternalAPIException('No hashrate found');
}

insert_new_hashrate($job, $account, $exchange, $currency, $data['256']['hashrate'] / 1e6 /* H/s -> MH/s */);
