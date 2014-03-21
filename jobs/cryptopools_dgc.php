<?php

/**
 * CryptoPools DGC pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "cryptopools_dgc";
$currency = 'dgc';
$table = "accounts_cryptopools_dgc";
$api_url = "http://dgc.cryptopools.com/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
