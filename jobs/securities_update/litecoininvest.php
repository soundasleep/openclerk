<?php

/**
 * A batch script to get all current Litecoininvest securities and queue them up for ticker values,
 * so we will have historical data even if no user has the security yet.
 */

$exchange = "securities_litecoininvest";
$currency = 'ltc';

// get the API data
$json = crypto_json_decode(crypto_get_contents(crypto_wrap_url('https://www.litecoininvest.com/api/ticker')));

foreach ($json as $security => $data) {

	crypto_log("Parsing security '" . htmlspecialchars($security) . "'");

	// we now have a new value
	$balance = $data['bid'];
	// also available: ask, latest, outstanding, 24h_vol, etc

	// if this security has a balance of 0, then it's worthless and it's not really
	// worth saving into the database
	if ($balance == 0) {
		crypto_log("Security '" . htmlspecialchars($security) . "' had a bid of 0: ignoring");
		continue;
	}

	$q = db()->prepare("SELECT * FROM securities_litecoininvest WHERE name=?");
	$q->execute(array($security));
	$security_def = $q->fetch();
	if (!$security_def) {
		// need to insert a new security definition, so we can later get its value
		// we can't calculate the value of this security yet
		crypto_log("No securities_litecoininvest definition existed for '" . htmlspecialchars($security) . "': adding in new definition");
		$q = db()->prepare("INSERT INTO securities_litecoininvest SET name=?");
		$q->execute(array($security));
		$security_def = array(
			'name' => $security,
			'id' => db()->lastInsertId(),
		);
	}

	// since we already have bid data here, we might as well save it for free
	insert_new_balance($job, $security_def, $exchange, $currency, $balance);

}
