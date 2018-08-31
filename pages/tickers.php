<?php

/**
 * Admin page to export a CSV list of exchange rates for a given exchange/currency pair.
 */

$exchange = require_get("exchange");

$pairs = array();

$q = db()->prepare("SELECT currency1, currency2 FROM ticker_historical WHERE exchange=? GROUP BY currency1, currency2");
$q->execute(array($exchange));
while ($row = $q->fetch()) {
  $pairs[] = $row;
}

$q = db()->prepare("SELECT currency1, currency2 FROM ticker WHERE exchange=? GROUP BY currency1, currency2");
$q->execute(array($exchange));
while ($row = $q->fetch()) {
  $pairs[] = $row;
}

$result = array(
  'pairs' => $pairs,
);

header('Content-Type: application/json');
echo json_encode($result);

