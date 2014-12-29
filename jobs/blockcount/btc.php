<?php

/**
 * Blockchain job (BTC) block count.
 */

$currency = \DiscoveredComponents\Currencies::getInstance("btc");
$balance = $currency->getBlockCount($logger);

insert_new_block_count($job, $currency->getCode(), $balance);
