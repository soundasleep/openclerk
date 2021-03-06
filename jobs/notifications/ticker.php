<?php

/**
 * Ticker check for notifications.
 * Finds out the changed values for a particular ticker.
 */

// get the most recent value
$ticker = get_latest_ticker($account['exchange'], $account['currency1'], $account['currency2']);
if (!$ticker) {
  // TODO maybe support failing for notifications, to disable notifications for e.g. accounts that no longer exist?
  // probably better to make sure that we can never *have* a referenced account that never exists
  throw new JobException("Could not find any recent ticker values for " . $account['exchange'] . " " . $account['currency1'] . "/" . $account['currency2']);
}

// TODO currently only supports last_trade, could also support buy/sell/volume
$current_value = $ticker['last_trade'];

// what was the last value?
// may need to generate this if no value exists, but hopefully this only occurs very rarely,
// since this may be a very heavy query
if ($notification['last_value'] === null) {
  crypto_log("No last value found: retrieving");

  // get the query string for this interval
  $periods = get_permitted_notification_periods();
  if (!isset($periods[$notification['period']]['interval'])) {
    throw new JobException("Unknown job period '" . $notification['period'] . "'");
  }
  $period = $periods[$notification['period']]['interval'];


  $q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND created_at <= DATE_SUB(NOW(), $period) ORDER BY id DESC LIMIT 1");
  $q->execute(array(
    "exchange" => $account['exchange'],
    "currency1" => $account['currency1'],
    "currency2" => $account['currency2'],
  ));
  $last = $q->fetch();
  if (!$last) {
    throw new JobException("Could not find any last values for " . $account['exchange'] . " " . $account['currency1'] . "/" . $account['currency2']);
  }
  $notification['last_value'] = $last['last_trade'];
}

// other parameters
$value_label = get_currency_abbr($account['currency1']) . "/" . get_currency_abbr($account['currency2']);
