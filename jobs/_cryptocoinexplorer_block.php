<?php

/**
 * More horrible HTML to parse.
 */

$html = crypto_get_contents(crypto_wrap_url(get_site_config($currency . '_block_url_html')));
require(__DIR__ . '/../vendor/soundasleep/html5lib-php/library/HTML5/Parser.php');

// this HTML is totally messed up and invalid; try to clean it up
$html = preg_replace("/&([a-z]+)/im", "", $html);
$html = preg_replace("/class'/im", "class='", $html);

$dom = HTML5_Parser::parse($html);

// now load as XML
$xml = new SimpleXMLElement($dom->saveXML());

$x = $xml->xpath('//div[contains(@id,"block-button")]/.');
$block_string = (string) $x[0];
if (!$block_string) {
	throw new ExternalAPIException("Could not load block number from page");
}
$bits = explode(":", $block_string);
$block = $bits[count($bits)-1];
if (!is_numeric($block)) {
	throw new ExternalAPIException("Block ID was not numeric: " . $block);
}

crypto_log("Current $currency block number: " . number_format($block));

// disable old instances
$q = db()->prepare("UPDATE $block_table SET is_recent=0 WHERE is_recent=1");
$q->execute();

// we have a balance; update the database
$q = db()->prepare("INSERT INTO $block_table SET blockcount=:count,is_recent=1");
$q->execute(array(
	"count" => $block,
));
crypto_log("Inserted new $block_table id=" . db()->lastInsertId());
