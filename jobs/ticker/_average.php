<?php

/**
 * Inputs: $recents as an array of last_trade, ask, bid, volume etc for a particular day.
 * This can be from ticker_recent or from historical trade data for a day.
 * Outputs: $pairs of ticker values
 */

$pairs = array();
foreach ($recents as $recent) {
  $key = $recent['currency1'] . $recent['currency2'];
  if (!isset($pairs[$key])) {
    $pairs[$key] = array(
      'total_last_trade' => 0,
      'total_volume' => 0,
      'total_ask' => 0,
      'total_bid' => 0,
      'total_volume_ask' => 0,
      'total_volume_bid' => 0,
      'currency1' => $recent['currency1'],
      'currency2' => $recent['currency2'],
      'exchanges' => 0,
    );
  }
  $pairs[$key]['total_last_trade'] += ($recent['last_trade'] * $recent['volume']);
  $pairs[$key]['total_volume'] += $recent['volume'];
  $pairs[$key]['exchanges'] ++;
  if ($recent['ask'] > 0) {
    $pairs[$key]['total_ask'] += ($recent['ask'] * $recent['volume']);
    $pairs[$key]['total_volume_ask'] += $recent['volume'];
  }
  if ($recent['bid'] > 0) {
    $pairs[$key]['total_bid'] += ($recent['bid'] * $recent['volume']);
    $pairs[$key]['total_volume_bid'] += $recent['volume'];
  }
}
