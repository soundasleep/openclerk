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

crypto_log("Bitnz rate for " . $rl['cur1'] . "/" . $rl['cur2'] . ": " . $bitnz['price']);

// update old recent values
$q = db()->prepare("UPDATE ticker SET is_recent=0 WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2");
$q->execute(array(
	"exchange" => $exchange['name'],
	"currency1" => strtolower($rl['cur1']),
	"currency2" => strtolower($rl['cur2']),
));

// all other data from today is now old
// NOTE if the system time changes between the next two commands, then we may erraneously
// specify that there is no valid daily data. one solution is to specify NOW() as $created_at rather than
// relying on MySQL
$q = db()->prepare("UPDATE ticker SET is_daily_data=0 WHERE is_daily_data=1 AND exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND
	date_format(created_at, '%d-%m-%Y') = date_format(now(), '%d-%m-%Y')");
$q->execute(array(
	"exchange" => $exchange['name'],
	"currency1" => strtolower($rl['cur1']),
	"currency2" => strtolower($rl['cur2']),
));

// insert in new ticker value
$q = db()->prepare("INSERT INTO ticker SET is_recent=1, exchange=:exchange, currency1=:currency1, currency2=:currency2, last_trade=:last_trade, buy=:buy, sell=:sell, job_id=:job_id, is_daily_data=1");
$q->execute(array(
	"exchange" => $exchange['name'],
	"currency1" => strtolower($rl['cur1']),
	"currency2" => strtolower($rl['cur2']),
	"last_trade" => $bitnz['price'],
	"buy" => $bitnz['buy'][0]['price'],
	"sell" => $bitnz['sell'][0]['price'],
	"job_id" => $job['id'],
	// no volume data... this must be through SOME API
));

crypto_log("Inserted new ticker id=" . db()->lastInsertId());
