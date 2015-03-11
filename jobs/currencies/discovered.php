<?php

/**
 * Discovered account currencies.
 */

if (!$exchange) {
  throw new JobException("No exchange defined");
}

$factory = new \Core\DiscoveredCurrencyFactory();
$instance = \DiscoveredComponents\Accounts::getInstance($exchange);
$currencies = $instance->fetchSupportedCurrencies($factory, $logger);

$logger->info("Found " . count($currencies) . " currencies: " . implode(", ", $currencies));

$persistent = new \Core\PersistentAccountType($instance, db());
$persistent->storeSupportedCurrencies($currencies, $logger);
