<?php

/**
 * Discovered markets job.
 */

if (!$exchange) {
  throw new JobException("No exchange defined");
}

$instance = \DiscoveredComponents\Exchanges::getInstance($exchange);
$markets = $instance->fetchMarkets($logger);
$result = array();
foreach ($markets as $m) {
  $result[] = implode("/", $m);
}
$logger->info("Found " . count($markets) . " markets: " . implode(", ", $result));

$persistent = new \Core\PersistentExchange($instance, db());
$persistent->storeMarkets($markets, $logger);
