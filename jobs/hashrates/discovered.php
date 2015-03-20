<?php

/**
 * Discovered account hashrate currencies.
 */

if (!$exchange) {
  throw new JobException("No exchange defined");
}

$factory = new \Core\DiscoveredCurrencyFactory();
$instance = \DiscoveredComponents\Accounts::getInstance($exchange);
$currencies = $instance->fetchSupportedHashrateCurrencies($factory, $logger);

$logger->info("Found " . count($currencies) . " hashrate currencies: " . implode(", ", $currencies));

$persistent = new \Core\PersistentAccountTypeHashrates($instance, db());
$persistent->storeSupportedHashrateCurrencies($currencies, $logger);
