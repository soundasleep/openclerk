<?php

/**
 * VirtEx ticker job.
 * VirtEx does not actually have an API (yet), so we have to do screen scraping.
 */

$exchange = "virtex";
$currency1 = "cad";
$currency2 = "btc";

require(__DIR__ . '../../inc/html5lib/Parser.php');
// this doesn't return valid XHTML, so we use the HTML5 parser as a temporary solution
$html = crypto_get_contents(crypto_wrap_url("https://www.cavirtex.com/orderbook"));
$dom = HTML5_Parser::parse($html);

// now load as XML
// crypto_log($dom->saveXML());
$xml = new SimpleXMLElement($dom->saveXML());
$xml->registerXPathNamespace('html', 'http://www.w3.org/1999/xhtml');

function virtex_table_first_row($xml, $table_id, $expected = 4) {
	$orderbook = $xml->xpath("//html:div[@id='$table_id']/html:table/html:tbody/html:tr");
	if (!$orderbook) {
		$orderbook = $xml->xpath("//html:table[@id='$table_id']/html:tbody/html:tr");
	}
	$last_trade = false;
	foreach ($orderbook as $ob) {
		$ob->registerXPathNamespace('html', 'http://www.w3.org/1999/xhtml');
		if ($ob->xpath("html:th")) {
			continue;
		}
		// try <b> first
		$queries = array("html:td/html:b", "html:td");
		foreach ($queries as $q) {
			$nodes = $ob->xpath($q);
			if (!$nodes)
				continue;
			crypto_log("First $table_id row: " . implode(",", $nodes));
			if (count($nodes) == $expected) {
				$r = array();
				for ($i = 0; $i < $expected; $i++) {
					$r[] = (string) $nodes[$i];
				}
				return $r;
			} else {
				throw new ExternalAPIException("Expected $expected rows in $table_id table, found " . implode(",", $nodes));
			}
		}
	}
	throw new ExternalAPIException("Found no first row in table '$table_id'");
}

$last_trade_row = virtex_table_first_row($xml, 'orderbook_trades');
crypto_log("Found last_trade " . print_r($last_trade_row, true));

$buy_row = virtex_table_first_row($xml, 'orderbook_buy');
crypto_log("Found buy " . print_r($buy_row, true));

$sell_row = virtex_table_first_row($xml, 'orderbook_sell');
crypto_log("Found sell " . print_r($sell_row, true));

$volume_row = virtex_table_first_row($xml, 'ticker', 5);
crypto_log("Found volume " . print_r($volume_row, true));

$last_trade = $last_trade_row[3];
$buy = $buy_row[2];
$sell = $sell_row[2];
$volume = preg_replace("#[^0-9\.]#", "", $volume_row[4]);

crypto_log("VirtEx rate for CAD/BTC: last_trade=$last_trade, buy=$buy, sell=$sell, volume=$volume");

// update old recent values
$q = db()->prepare("UPDATE ticker SET is_recent=0 WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2");
$q->execute(array(
	"exchange" => $exchange,
	"currency1" => $currency1,
	"currency2" => $currency2,
));

// all other data from today is now old
// NOTE if the system time changes between the next two commands, then we may erraneously
// specify that there is no valid daily data. one solution is to specify NOW() as $created_at rather than
// relying on MySQL
$q = db()->prepare("UPDATE ticker SET is_daily_data=0 WHERE is_daily_data=1 AND exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND
	date_format(created_at, '%d-%m-%Y') = date_format(now(), '%d-%m-%Y')");
$q->execute(array(
	"exchange" => $exchange,
	"currency1" => $currency1,
	"currency2" => $currency2,
));

// insert in new ticker value
$q = db()->prepare("INSERT INTO ticker SET is_recent=1, exchange=:exchange, currency1=:currency1, currency2=:currency2, last_trade=:last_trade, buy=:buy, sell=:sell, volume=:volume, job_id=:job_id, is_daily_data=1");
$q->execute(array(
	"exchange" => $exchange,
	"currency1" => $currency1,
	"currency2" => $currency2,
	"last_trade" => $last_trade,
	"buy" => $buy,
	"sell" => $sell,
	"volume" => $volume,		// this volume is in BTC
	"job_id" => $job['id'],
));

crypto_log("Inserted new ticker id=" . db()->lastInsertId());