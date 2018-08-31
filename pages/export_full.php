<?php

/**
 * Admin page to export a CSV list of exchange rates for a given exchange/currency pair.
 */

$exchange = require_get("exchange");
$currency1 = require_get("currency1");
$currency2 = require_get("currency2");
$page = (int) require_get("page");
$per_page = 300;
$offset = $page * $per_page;

if ($page == 60) {
  // stop at page 60 please
  echo json_encode(array());
  return;
}

$q = db()->prepare("SELECT * FROM ticker_historical WHERE exchange=? AND currency1=? AND currency2=? LIMIT $offset,$per_page");
$q->execute(array($exchange, $currency1, $currency2));

$result = array();
while ($row = $q->fetch()) {
  $result[] = $row;
}

$q = db()->prepare("SELECT * FROM ticker WHERE exchange=? AND currency1=? AND currency2=? LIMIT $offset,$per_page");
$q->execute(array($exchange, $currency1, $currency2));

while ($row = $q->fetch()) {
  $result[] = $row;
}

header('Content-Type: application/json');
echo json_encode($result);

