<?php

/**
 * Discovered currency block count.
 */

if (!$currency) {
  throw new JobException("No currency defined");
}

$instance = \DiscoveredComponents\Currencies::getInstance($currency);
$balance = $instance->getBlockCount($logger);

insert_new_block_count($job, $instance->getCode(), $balance);
