<?php

/**
 * Small Time Miner Megacoin pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "smalltimeminer_mec";
$currency = 'mec';
$table = "accounts_smalltimeminer_mec";
$api_url = "http://meg.smalltimeminer.com/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
