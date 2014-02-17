<?php

/**
 * Nut2Pools FTC pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "nut2pools_ftc";
$currency = 'ftc';
$table = "accounts_nut2pools_ftc";
$api_url = "https://ftc.nut2pools.com/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
