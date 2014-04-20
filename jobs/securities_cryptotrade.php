<?php

/**
 * Crypto-Trade securities ticker job.
 */

$exchange = "securities_crypto-trade";

// get the relevant security
$q = db()->prepare("SELECT * FROM securities_cryptotrade WHERE id=?");
$q->execute(array($job['arg_id']));
$security = $q->fetch();
if (!$security) {
	throw new JobException("Cannot find a $exchange security " . $job['arg_id'] . " for user " . $job['user_id']);
}

$cur1 = $security['currency'];
$cur2 = strtolower($security['name']);

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://crypto-trade.com/api/1/ticker/" . $cur2 . "_" . $cur1)));

if (!isset($rates['data']['max_bid'])) {
	if (isset($rates['error'])) {
		throw new ExternalAPIException("Could not find $cur1/$cur2 rate for $exchange: " . htmlspecialchars($rates['error']));
	}

	throw new ExternalAPIException("No $cur1/$cur2 rate for $exchange");
}

crypto_log("Security $cur1/$cur2 balance: " . $rates['data']['max_bid']);

// insert new balance
insert_new_balance($job, $security, 'securities_crypto-trade', $security['currency'], $rates['data']['max_bid']);
