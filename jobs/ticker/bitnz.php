<?php

/**
 * BitNZ ticker job.
 * BitNZ does not actually have an API, so we have to do screen scraping.
 */

$bitnz = array();
require(__DIR__ . '/../../inc/html5lib/Parser.php');
$html = crypto_get_contents(crypto_wrap_url("https://bitnz.com/orders"));
$dom = HTML5_Parser::parse($html);

// now load as XML
$xml = new SimpleXMLElement($dom->saveXML());
$x = $xml->xpath('//div[contains(@class,"sidebar")]/.');
$bitnz["price"] = preg_replace('#[^0-9\.]#im', '', (string) $x[0]);

$buy = $xml->xpath("//*/h4[text()='Buy']/..//td");
$bitnz["buy"] = array();
for ($i = 0; $i < count($buy); $i += 2) {
	$bitnz["buy"][] = array("price" => (string) $buy[$i], "btc" => (string) $buy[$i+1]);
}
$buy = $xml->xpath("//*/h4[text()='Sell']/..//td");
$bitnz["sell"] = array();
for ($i = 0; $i < count($buy); $i += 2) {
	$bitnz["sell"][] = array("price" => (string) $buy[$i], "btc" => (string) $buy[$i+1]);
}

$rl = array('cur1' => 'nzd', 'cur2' => 'btc');

if (!isset($bitnz['price'])) {
	throw new ExternalAPIException("No " . $rl['cur1'] . "/" . $rl['cur2'] . " rate for Bitnz");
}

if ($bitnz['price'] == 0) {
	// don't insert in a zero balance
	throw new ExternalAPIException("Cannot insert in a zero value for BitNZ");
}

// insert in new ticker value
insert_new_ticker($job, $exchange, strtolower($rl['cur1']), strtolower($rl['cur2']), array(
	"last_trade" => $bitnz['price'],
	// BitNZ reports Buy/Sell incorrectly
	"bid" => $bitnz['buy'][0]['price'],
	"ask" => $bitnz['sell'][0]['price'],
	// "volume" => $obj['volume'], - BitNZ does not provide volume data, but there must be an API somewhere?
));
