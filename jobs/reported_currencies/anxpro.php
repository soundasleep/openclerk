<?php

/**
 * ANXPRO reported currencies job.
 * ANXPRO does not actually provide an API for supported coins, so we use an anonmyous user
 * to get pairs through the trade API.
 */

$account = array(
  'api_key' => get_site_config('anxpro_example_api_key'),
  'api_secret' => get_site_config('anxpro_example_api_secret'),
);

require(__DIR__ . "/../_anxpro.php");

$info = anxpro_query($account['api_key'], $account['api_secret'], 'money/info');
if (isset($info['error'])) {
  throw new ExternalAPIException("API returned error: '" . $info['error'] . "'");
}

$currencies = array();
foreach ($info['data']['Wallets'] as $currency => $ignored) {
  $currencies[] = strtolower($currency);
}

crypto_log("Found reported currencies " . print_r($currencies, true));

// update the database
$q = db()->prepare("DELETE FROM reported_currencies WHERE exchange=?");
$q->execute(array($exchange['name']));

foreach ($currencies as $currency) {
  $q = db()->prepare("INSERT INTO reported_currencies SET exchange=?, currency=?");
  $q->execute(array($exchange['name'], $currency));
}
