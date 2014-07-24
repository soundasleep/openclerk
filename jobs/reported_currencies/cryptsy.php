<?php

/**
 * Cryptsy reported currencies job (#121).
 * Also update the list of currencies that can be voted on (#264).
 */

$market = crypto_json_decode(crypto_get_contents(crypto_wrap_url("http://pubapi.cryptsy.com/api.php?method=marketdatav2")));

$currencies = array();
$currency_votes = array();
foreach ($market['return']['markets'] as $market) {
	$currencies[] = strtolower($market['primarycode']);
	$currencies[] = strtolower($market['secondarycode']);
	$currency_votes[$market['primarycode']] = $market['primaryname'];
	$currency_votes[$market['secondarycode']] = $market['secondaryname'];
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

// update the list of votable currencies
foreach ($currency_votes as $code => $name) {
	// don't add currencies we already support
	if (in_array(get_currency_key($code), get_all_currencies())) {
		continue;
	}

	$q = db()->prepare("SELECT * FROM vote_coins WHERE code=? LIMIT 1");
	$q->execute(array($code));
	if (!$q->fetch()) {
		$q = db()->prepare("INSERT INTO vote_coins SET code=?, title=?");
		$q->execute(array($code, $name));
		crypto_log("Added new vote currency $code/$name.");
	}
}
