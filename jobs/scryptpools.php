<?php

/**
 * scryptpools.com pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "scryptpools";
$currency = 'dog';
$table = "accounts_scryptpools";
$api_url = "http://doge.scryptpools.com/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
