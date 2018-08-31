<?php

/**
 * Admin page to export a CSV list of exchange rates for a given exchange/currency pair.
 */

$exchange = require_get("exchange");
$currency1 = require_get("currency1");
$currency2 = require_get("currency2");

$q = db()->prepare("SELECT MIN(created_at) AS min, MAX(created_at) AS max, COUNT(*) AS c FROM ticker_historical WHERE exchange=? AND currency1=? AND currency2=?");
$q->execute(array($exchange, $currency1, $currency2));
$historical = $q->fetch();

$q = db()->prepare("SELECT MIN(created_at) AS min, MAX(created_at) AS max, COUNT(*) AS c FROM ticker WHERE exchange=? AND currency1=? AND currency2=?");
$q->execute(array($exchange, $currency1, $currency2));
$ticker = $q->fetch();

$q = db()->prepare("SELECT currency1, currency2 FROM ticker WHERE exchange=? GROUP BY currency1, currency2");
$q->execute(array($exchange));
$pairs = $q->fetchAll();

$result = array(
  'historical' => $historical,
  'ticker' => $ticker,
  'pairs' => $pairs,
);

header('Content-Type: application/json');
echo json_encode($result);

