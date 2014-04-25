<?php

/**
 * RapidHash DOGE pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "rapidhash_doge";
$currency = 'dog';
$table = "accounts_rapidhash_doge";
$api_url = "https://doge.rapidhash.net/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
