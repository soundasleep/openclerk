<?php

/**
 * HashFaster DOGE pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "hashfaster_doge";
$currency = 'dog';
$table = "accounts_hashfaster_doge";
$api_url = "http://doge.hashfaster.com/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
