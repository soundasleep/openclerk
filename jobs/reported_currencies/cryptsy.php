<?php

/**
 * Cryptsy reported currencies job (#121).
 */

$market = crypto_json_decode(crypto_get_contents(crypto_wrap_url("http://pubapi.cryptsy.com/api.php?method=marketdatav2")));

$currencies = array();
foreach ($market['return']['markets'] as $market) {
	$currencies[] = strtolower($market['primarycode']);
	$currencies[] = strtolower($market['secondarycode']);
}

$currencies = array_unique($currencies);
crypto_log("Found currencies " . implode(", ", $currencies));

// update the database
$q = db()->prepare("DELETE FROM reported_currencies WHERE exchange=?");
$q->execute(array($exchange['name']));

foreach ($currencies as $cur) {
	$q = db()->prepare("INSERT INTO reported_currencies SET exchange=?, currency=?");
	$q->execute(array($exchange['name'], $cur));
}
