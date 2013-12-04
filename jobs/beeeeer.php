<?php

/**
 * beeeeer pool balance job.
 */

$exchange = "beeeeer";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_beeeeer WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$raw = crypto_get_contents(crypto_wrap_url("http://beeeeer.org/user/" . urlencode($account['xpm_address'])));
if (!$raw) {
	throw new ExternalAPIException("Empty response");
}

// try and parse out the current balance
$key = "current balance:";
$balance = (float) substr($raw, strpos($raw, $key) + strlen($key), 30);
$currency = "xpm";

insert_new_balance($job, $account, $exchange, $currency, $balance);

