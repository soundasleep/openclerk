<?php

/**
 * Discovered currency difficulty job.
 */

if (!$currency) {
  throw new JobException("No currency defined");
}

$instance = \DiscoveredComponents\Currencies::getInstance($currency);
$value = $instance->getDifficulty($logger);

insert_new_difficulty($job, $instance->getCode(), $value);
