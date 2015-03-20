<?php

/**
 * A batch script to get all current Eligius accounts and their owners, and add
 * wallet values for owners as appropriate.
 */

$exchange = "eligius";
$currency = 'btc';

// get a list of all addresses
$content = crypto_get_contents(crypto_wrap_url('http://eligius.st/~luke-jr/raw/7/balances.json'));
if (!$content) {
  throw new ExternalAPIException("API returned empty data");
}
$data = json_decode($content, true);
if (!$data) {
  throw new ExternalAPIException("Invalid JSON");
}

// get all eligius bitcoin addresses
$q = db()->prepare("SELECT * FROM accounts_eligius");
$q->execute();
$accounts = $q->fetchAll();

foreach ($accounts as $account) {
  // is there a value for this account?
  if (!isset($data[$account['btc_address']])) {
    crypto_log("BTC address " . htmlspecialchars($account['btc_address']) . " had no associated balance addresses");
    continue;
  }

  // found one
  if (!isset($data[$account['btc_address']]['balance'])) {
    crypto_log("BTC address " . htmlspecialchars($account['btc_address']) . " had no balance");
    continue;
  }

  $balance = $data[$account['btc_address']]['balance'];
  // take away any estimates
  if (isset($data[$account['btc_address']]['included_balance_estimate'])) {
    $balance -= $data[$account['btc_address']]['included_balance_estimate'];
  }

  $job_copy = $job;
  $job_copy['user_id'] = $account['user_id'];   // replace user_id
  insert_new_balance($job_copy, $account, $exchange, $currency, $balance / 1e8 /* returned in satoshis */);
}

crypto_log("Processed " . number_format(count($accounts)) . " $exchange accounts.");
