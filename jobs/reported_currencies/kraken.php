<?php

/**
 * Kraken reported currencies job (#121).
 */

require(__DIR__ . "/../_kraken.php");

$pairs = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://api.kraken.com/0/public/AssetPairs")));
if (isset($pairs['error'][0]) && $pairs['error'][0]) {
	throw new ExternalAPIException("assetPairs returned " . htmlspecialchars($pairs['error'][0]));
}

$currencies = array();
foreach ($pairs['result'] as $key => $data) {
	$cur1 = strtolower(substr($key, 0, 4));
	try {
		$cur1 = map_iso4_name($cur1);
	} catch (JobException $e) {
		// this is fine, we just can't convert it
	}
	$currencies[$cur1] = $cur1;

	$cur2 = strtolower(substr($key, 4, 8));
	try {
		$cur2 = map_iso4_name($cur2);
	} catch (JobException $e) {
		// this is fine, we just can't convert it
	}
	$currencies[$cur2] = $cur2;
}

crypto_log("Found reported currencies " . implode(",", $currencies));

// update the database
$q = db()->prepare("DELETE FROM reported_currencies WHERE exchange=?");
$q->execute(array($exchange['name']));

foreach ($currencies as $currency) {
	$q = db()->prepare("INSERT INTO reported_currencies SET exchange=?, currency=?");
	$q->execute(array($exchange['name'], $currency));
}
