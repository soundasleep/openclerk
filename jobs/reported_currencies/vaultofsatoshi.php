<?php

/**
 * Vault of Satoshi reported currencies job (#121).
 * Vault of Satoshi does not actually provide an API for supported coins, so we have to do screen scraping.
 */

$bitnz = array();
require(__DIR__ . '/../../inc/html5lib/Parser.php');
$html = crypto_get_contents(crypto_wrap_url("https://www.vaultofsatoshi.com/charts"));
$dom = HTML5_Parser::parse($html);

// now load as XML
$xml = new SimpleXMLElement($dom->saveXML());

// find all currencies
$currencies = array();
$x = $xml->xpath('//select[contains(@id,"coinPaymentCurrencies") or contains(@id,"coinOrderCurrencies")]/option');
crypto_log("Found currencies " . print_r($x, true));
foreach ($x as $option) {
	$currencies[] = strtolower((string) $option["val"]);
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
