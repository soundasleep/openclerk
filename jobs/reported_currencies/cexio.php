<?php

/**
 * CEX.io reported currencies job (#121).
 * CEX.io does not actually provide an API for supported coins, so we have to do screen scraping.
 */

$bitnz = array();
require(__DIR__ . '/../../vendor/soundasleep/html5lib-php/library/HTML5/Parser.php');
$html = crypto_get_contents(crypto_wrap_url("https://cex.io/"));
$dom = HTML5_Parser::parse($html);

// now load as XML
$xml = new SimpleXMLElement($dom->saveXML());

// find all currencies
$currencies = array();
$x = $xml->xpath('//div/a[contains(@class,"btn")]');
crypto_log("Found currencies " . print_r($x, true));

foreach ($x as $link) {
	$pair = explode("/", strtolower((string) $link));
	if (count($pair) == 2) {
		$currencies[] = trim($pair[0]);
		$currencies[] = trim($pair[1]);
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
