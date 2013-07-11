<?php

/**
 * A batch script to get all current BTCT securities and queue them up for ticker values,
 * so we will have historical data even if no user has the security yet.
 */

$exchange = "securities_btct";
$currency = 'btc';

// get the API data
$content = crypto_get_contents(crypto_wrap_url('https://btct.co/api/ticker'));
if (!$content) {
	throw new ExternalAPIException("API returned empty data");
}
$json = json_decode($content, true);
if (!$json) {
	throw new ExternalAPIException("JSON was invalid");
}

foreach ($json as $security => $data) {

	// we now have a new value
	$balance = $data['bid'];
	if ($balance == "--") {
		// if a security has been removed, 'bid' will return '--'
		// TODO this might be a good place to consider removing the security from the database
		$balance = 0;
	}

	// if this security has a balance of 0, then it's worthless and it's not really
	// worth saving into the database
	if ($balance == 0) {
		crypto_log("Security '" . htmlspecialchars($security) . "' had a bid of 0: ignoring");
		continue;
	}

	$q = db()->prepare("SELECT * FROM securities_btct WHERE name=?");
	$q->execute(array($security));
	$security_def = $q->fetch();
	if (!$security_def) {
		// need to insert a new security definition, so we can later get its value
		// we can't calculate the value of this security yet
		crypto_log("No securities_btct definition existed for '" . htmlspecialchars($security) . "': adding in new definition");
		$q = db()->prepare("INSERT INTO securities_btct SET name=?");
		$q->execute(array($security));
		$security_def = array(
			'name' => $security,
			'id' => db()->lastInsertId(),
		);
	}

	// since we already have bid data here, we might as well save it for free
	insert_new_balance($job, $security_def, $exchange, $currency, $balance);

}
