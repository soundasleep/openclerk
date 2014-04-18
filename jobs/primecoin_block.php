<?php

/**
 * Get current Primecoin block number. Used to deduct unconfirmed transactions
 * when retrieving Feathercoin balances.
 * Since this isn't based off Abe, we scrape HTML instead (ergh).
 */

$html = crypto_get_contents(crypto_wrap_url(get_site_config('xpm_block_url_html')));
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

crypto_log("Current Novacoin block number: " . number_format($block));

// disable old instances
$q = db()->prepare("UPDATE primecoin_blocks SET is_recent=0 WHERE is_recent=1");
$q->execute();

// we have a balance; update the database
$q = db()->prepare("INSERT INTO primecoin_blocks SET blockcount=:count,is_recent=1");
$q->execute(array(
	"count" => $block,
));
crypto_log("Inserted new primecoin_blocks id=" . db()->lastInsertId());
