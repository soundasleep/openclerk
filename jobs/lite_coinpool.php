<?php

/**
 * lite.coin-pool.com balance job.
 */

$exchange = "lite_coinpool";
$url = "http://lite.coin-pool.com/api.php?api_key=";
$currency = 'ltc';
$table = "accounts_lite_coinpool";

require(__DIR__ . "/_mmcfe_pool.php");
