<?php

/**
 * BTC Trading Co. securities value job.
 * Retrieves the current 'bid' value for a particular security.
 */

$exchange = "securities_btct";
$currency = 'btc';

// get the relevant security
$q = db()->prepare("SELECT * FROM securities_btct WHERE id=?");
$q->execute(array($job['arg_id']));
$security = $q->fetch();
if (!$security) {
	throw new JobException("Cannot find a $exchange security " . $job['arg_id'] . " for user " . $job['user_id']);
}

$content1 = false;
$content = crypto_get_contents(crypto_wrap_url('https://btct.co/api/ticker/' . urlencode($security['name'])));
if ($content === "0") {
	// is this actually a Litecoinglobal bond?
	$content1 = crypto_get_contents(crypto_wrap_url('https://www.litecoinglobal.com/api/ticker/' . urlencode($security['name'])));
	if ($content1) {
		// this security should never have existed (can exist if someone inserts in the wrong API key)
		crypto_log("btct security " . htmlspecialchars($security['name']) . " is actually a litecoinglobal security: removing invalid security");

		$q = db()->prepare("DELETE FROM securities_btct WHERE id=?");
		$q->execute(array($job['arg_id']));

	} else {
		throw new ExternalAPIException("API returned empty data 0");
	}
}
if (!$content1) {
	if (!$content) {
		throw new ExternalAPIException("API returned empty data");
	}

	// fix broken JSON
	$content = preg_replace("#<!--[^>]+-->#", "", $content);

	$data = json_decode($content, true);
	if (!$data) {
		if (substr($content, 0, 1) == "<") {
			throw new ExternalAPIException("Unexpectedly received HTML instead of JSON");
		} else {
			throw new ExternalAPIException("Invalid JSON detected");
		}
	}

	// we now have a new value
	$balance = $data['bid'];
	if ($balance == "--") {
		// if a security has been removed, 'bid' will return '--'
		// TODO this might be a good place to consider removing the security from the database
		$balance = 0;
	}

	insert_new_balance($job, $security, $exchange, $currency, $balance);

}
