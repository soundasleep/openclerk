<?php

/**
 * HashFaster LTC pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "hashfaster_ltc";
$currency = 'ltc';
$table = "accounts_hashfaster_ltc";
$api_url = "https://ltc.hashfaster.com/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
