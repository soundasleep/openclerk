<?php

/**
 * dogepool.pw pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "dogepoolpw";
$currency = 'dog';
$table = "accounts_dogepoolpw";
$api_url = "http://dogepool.pw/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
