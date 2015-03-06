<?php

/**
 * Discovered account hashrate currencies.
 */

if (!$exchange) {
  throw new JobException("No exchange defined");
}

$instance = \DiscoveredComponents\Accounts::getInstance($exchange);
$currencies = $instance->fetchSupportedHashrateCurrencies($logger);

$logger->info("Found " . count($currencies) . " hashrate currencies: " . implode(", ", $currencies));

$persistent = new \Core\PersistentAccountTypeHashrates($instance, db());
$persistent->storeSupportedHashrateCurrencies($currencies, $logger);
