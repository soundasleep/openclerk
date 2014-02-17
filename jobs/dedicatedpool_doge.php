<?php

/**
 * dedicatedpool.com DOGE pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "dedicatedpool_doge";
$currency = 'dog';
$table = "accounts_dedicatedpool_doge";
$api_url = "http://doge.dedicatedpool.com/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
