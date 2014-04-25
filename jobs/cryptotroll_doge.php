<?php

/**
 * Cryptotroll DOGE pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "cryptotroll_doge";
$currency = 'dog';
$table = "accounts_cryptotroll_doge";
$api_url = "http://doge.cryptotroll.com/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
