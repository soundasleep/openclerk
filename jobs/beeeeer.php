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

$raw = crypto_get_contents(crypto_wrap_url("http://beta.beeeeer.org/index.php?p=user&s=xpm&a=" . urlencode($account['xpm_address'])));
if (!$raw) {
	throw new ExternalAPIException("Empty response");
}

// try and parse out the current balance
$key = "current balance:";
$balance_string = substr($raw, strpos($raw, $key) + strlen($key), 30);
// check balance is numeric to prevent issue #67 from occuring again
if (!preg_match("#^[0-9\.]+#i", $balance_string)) {
	throw new ExternalAPIException("Current balance was not numeric");
}
$balance = (float) $balance_string;		// will ignore any following characters
$currency = "xpm";

insert_new_balance($job, $account, $exchange, $currency, $balance);

