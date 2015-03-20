<?php

/**
 * Restore market average data for a particular day (#457).
 */

crypto_log("Restoring market average data for day " . $job['arg_id']);

$q = db()->prepare("SELECT * FROM ticker WHERE created_at_day=? AND is_daily_data=1 AND exchange <> 'average'");
$q->execute(array($job['arg_id']));
$recents = $q->fetchAll();

crypto_log("Found " . number_format(count($recents)) . " ticker instances to recreate average data");

$exchange = array(
  'name' => 'average',
);

require(__DIR__ . "/_average.php");

// we can now create ticker values as necessary
foreach ($pairs as $pair) {

  if ($pair['total_volume'] > 0) {

    // delete any old average data
    $q = db()->prepare("DELETE FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND created_at_day=TO_DAYS(:date)");
    $q->execute(array(
      "date" => $recents[0]['created_at'],
      "exchange" => $exchange['name'],
      "currency1" => $pair['currency1'],
      "currency2" => $pair['currency2'],
    ));

    crypto_log("Deleted any old average data");

    // insert in new ticker value
    $q = db()->prepare("INSERT INTO ticker SET exchange=:exchange, currency1=:currency1, currency2=:currency2, last_trade=:last_trade, bid=:bid, ask=:ask, volume=:volume, job_id=:job_id, is_daily_data=1, created_at=:date, created_at_day=TO_DAYS(:date)");
    $q->execute(array(
      "date" => $recents[0]['created_at'],
      "exchange" => $exchange['name'],
      "currency1" => $pair['currency1'],
      "currency2" => $pair['currency2'],
      "last_trade" => $pair['total_last_trade'] / $pair['total_volume'],
      "bid" => $pair['total_volume_bid'] > 0 ? $pair['total_bid'] / $pair['total_volume_bid'] : 0,
      "ask" => $pair['total_volume_ask'] > 0 ? $pair['total_ask'] / $pair['total_volume_ask'] : 0,
      "volume" => $pair['total_volume'],
      "job_id" => $job['id'],
    ));

    crypto_log("Inserted in new ticker ID " . db()->lastInsertId());
  }

  // no need to track average market count: this isn't used in historical data

}
