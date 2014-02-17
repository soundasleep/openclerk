<?php

/**
 * TeamDoge pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "teamdoge";
$currency = 'dog';
$table = "accounts_teamdoge";
$api_url = "https://teamdoge.com/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
