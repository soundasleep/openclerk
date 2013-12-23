<?php

/**
 * HashFaster FTC pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "hashfaster_ftc";
$currency = 'ftc';
$table = "accounts_hashfaster_ftc";
$api_url = "http://ftc.hashfaster.com/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
