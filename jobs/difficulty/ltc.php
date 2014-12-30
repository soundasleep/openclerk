<?php

/**
 * Litecoin difficulty job.
 */

$currency = \DiscoveredComponents\Currencies::getInstance("ltc");
$value = $currency->getDifficulty($logger);

insert_new_difficulty($job, $currency->getCode(), $value);
