<?php

/**
 * Crypto-Trade reported currencies job (#121).
 * Crypto-Trade does not actually provide an API for supported coins, so we have to do screen scraping.
 */

$bitnz = array();
require(__DIR__ . '/../../vendor/soundasleep/html5lib-php/library/HTML5/Parser.php');
$html = crypto_get_contents(crypto_wrap_url("https://crypto-trade.com/trade"));
$dom = HTML5_Parser::parse($html);

// now load as XML
$xml = new SimpleXMLElement($dom->saveXML());
$xml->registerXPathNamespace('html', 'http://www.w3.org/1999/xhtml');

// find all currencies
$currencies = array();
$x = $xml->xpath('//html:div[contains(@id,"trade-pairs")]/html:a');
crypto_log("Found currencies " . print_r($x, true));
foreach ($x as $option) {
	$pairs = explode("/", strtolower((string) $option));
	if (count($pairs) == 2) {
		$currencies[] = trim($pairs[0]);
		$currencies[] = trim($pairs[1]);
	}
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
