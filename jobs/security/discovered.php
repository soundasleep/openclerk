<?php

/**
 * Get the latest value for a given security, and store it in
 * security_ticker and security_ticker_recent.
 */

if (!$exchange) {
  throw new JobException("No exchange defined");
}

// get the relevant security
$q = db()->prepare("SELECT * FROM security_exchange_securities WHERE id=?");
$q->execute(array($job['arg_id']));
$security = $q->fetch();
if (!$security) {
  throw new JobException("Cannot find a security " . $job['arg_id']);
}

$instance = \DiscoveredComponents\SecurityExchanges::getInstance($exchange);

$rates = $instance->fetchRates($security['security'], $logger);
insert_new_security_ticker($job, $security, $rates);
