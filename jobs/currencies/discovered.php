<?php

/**
 * Discovered account currencies.
 */

if (!$exchange) {
  throw new JobException("No exchange defined");
}

$instance = \DiscoveredComponents\Accounts::getInstance($exchange);
$currencies = $instance->fetchSupportedCurrencies($logger);

$logger->info("Found " . count($currencies) . " currencies: " . implode(", ", $currencies));

$persistent = new \Core\PersistentAccountType($instance, db());
$persistent->storeSupportedCurrencies($currencies, $logger);
