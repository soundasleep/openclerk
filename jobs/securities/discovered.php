<?php

/**
 * Discovered security exchange securities,
 * and store latest security values.
 */

if (!$exchange) {
  throw new JobException("No exchange defined");
}

$instance = \DiscoveredComponents\SecurityExchanges::getInstance($exchange);
$securities = $instance->fetchSecurities($logger);

$keys = array();
foreach ($securities as $sec) {
  $keys[] = $sec['currency'] . "/" . $sec['security'];
}

$logger->info("Found " . count($securities) . " securities: " . implode(", ", $keys));

$persistent = new \Core\PersistentSecurityExchange($instance, db());
$persistent->storeSupportedSecurities($securities, $logger);
