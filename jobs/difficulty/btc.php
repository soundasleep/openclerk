<?php

/**
 * Blockchain job (BTC) difficulty.
 */

$currency = \DiscoveredComponents\Currencies::getInstance("btc");
$value = $currency->getDifficulty($logger);

insert_new_difficulty($job, $currency->getCode(), $value);
