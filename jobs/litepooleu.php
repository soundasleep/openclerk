<?php

/**
 * Litepool (litepool.eu) pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "litepooleu";
$currency = 'ltc';
$table = "accounts_litepooleu";
$api_url = "http://litepool.eu/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
