<?php

/**
 * Mining Foreman LTC balance job.
 */

$exchange = "miningforeman";
$url = "http://www.mining-foreman.org/api?api_key=";
$currency = 'ltc';
$table = "accounts_miningforeman";

require(__DIR__ . "/_mmcfe_pool.php");
