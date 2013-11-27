<?php

/**
 * Cryptostocks securities value job.
 * Retrieves the current 'bid' value for a particular security.
 */

$exchange = "securities_cryptostocks";

// get the relevant security
$q = db()->prepare("SELECT * FROM securities_cryptostocks WHERE id=?");
$q->execute(array($job['arg_id']));
$security = $q->fetch();
if (!$security) {
	throw new JobException("Cannot find a $exchange security " . $job['arg_id'] . " for user " . $job['user_id']);
}

$content = crypto_get_contents(crypto_wrap_url('https://cryptostocks.com/api/get_security_info.json?ticker=' . urlencode($security['name'])));
if (!$content) {
	throw new ExternalAPIException("API returned empty data");
}

$data = json_decode($content, true);
if (!$data) {
	if (substr($content, 0, 1) == "<") {
		throw new ExternalAPIException("Unexpectedly received HTML instead of JSON");
	} else {
		throw new ExternalAPIException("Invalid JSON detected");
	}
}

// we now have a new value
$balance = $data['highest_bid'];
$currency = strtolower($data['currency']);

// this lets us keep track of shares in currencies we don't support yet
if (strlen($currency) != 3) {
	throw new ExternalAPIException("Currency $currency is not 3 characters long");
}

// update this security definition with the currency
$q = db()->prepare("UPDATE securities_cryptostocks SET currency=? WHERE id=?");
$q->execute(array($currency, $security['id']));
crypto_log("Updated security " . htmlspecialchars($security['name']) . " to currency $currency");

insert_new_balance($job, $security, $exchange, $currency, $balance);
