<?php

/**
 * d2 WDC balance job.
 */

$exchange = "d2_wdc";
$url = "https://wdc.d2.cc/api.php?api_key=";
$currency = 'wdc';
$table = "accounts_d2_wdc";

require(__DIR__ . "/_mmcfe_pool.php");
