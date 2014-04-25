<?php

/**
 * RapidHash VTC pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "rapidhash_vtc";
$currency = 'vtc';
$table = "accounts_rapidhash_vtc";
$api_url = "https://vtc.rapidhash.net/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
