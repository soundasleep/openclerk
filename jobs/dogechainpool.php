<?php

/**
 * Dogechain Pool pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "dogechainpool";
$currency = 'dog';
$table = "accounts_dogechainpool";
$api_url = "http://pool.dogechain.info/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
