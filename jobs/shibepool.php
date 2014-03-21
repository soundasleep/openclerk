<?php

/**
 * Shibe Pool pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "shibepool";
$currency = 'dog';
$table = "accounts_shibepool";
$api_url = "http://shibepool.com/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
