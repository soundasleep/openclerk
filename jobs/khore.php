<?php

/**
 * nvc.khore.org balance job.
 */

$exchange = "khore";
$url = "https://nvc.khore.org/api?api_key=";
$currency = 'nvc';
$table = "accounts_khore";

require(__DIR__ . "/_mmcfe_pool.php");
