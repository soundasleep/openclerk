<?php

/**
 * Bitstamp ticker job.
 */

$exchange = "bitstamp";
$currency1 = "usd";
$currency2 = "btc";

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://www.bitstamp.net/api/ticker/")));

if (!isset($rates['last'])) {
	throw new ExternalAPIException("No $currency1/$currency2 last rate for Vircurex");
}

crypto_log($exchange . " rate for $currency1/$currency2: " . $rates['last']);

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
	"last_trade" => $rates['last'],
	"buy" => $rates['bid'],
	"sell" => $rates['ask'],
	"volume" => $rates['volume'],
	"job_id" => $job['id'],
));

crypto_log("Inserted new ticker id=" . db()->lastInsertId());
