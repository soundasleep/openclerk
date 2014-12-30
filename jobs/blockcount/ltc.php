<?php

/**
 * Litecoin block count job.
 */

$currency = \DiscoveredComponents\Currencies::getInstance("ltc");
$balance = $currency->getBlockCount($logger);

insert_new_block_count($job, $currency->getCode(), $balance);
