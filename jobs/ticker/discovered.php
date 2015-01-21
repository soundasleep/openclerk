<?php

/**
 * Discovered exchange ticker job.
 */

if (!$exchange) {
  throw new JobException("No exchange defined");
}

$instance = \DiscoveredComponents\Exchanges::getInstance($exchange);
$rates = $instance->fetchAllRates($logger);
$logger->info("Found " . count($rates) . " rates from exchange");

foreach ($rates as $code => $rate) {
  $currency1 = $rate['currency1'];
  $currency2 = $rate['currency2'];

  if (!in_array($currency1, get_all_currencies())) {
    $logger->info("Ignoring currency '$currency1': not a supported currency");
    continue;
  }
  if (!in_array($currency2, get_all_currencies())) {
    $logger->info("Ignoring currency '$currency2': not a supported currency");
    continue;
  }

  insert_new_ticker($job, array("name" => $exchange), $currency1, $currency2, $rate);
}
