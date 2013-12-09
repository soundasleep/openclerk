<?php

/**
 * WeMineLTC balance job.
 */

$exchange = "wemineltc";
$url = "https://www.wemineltc.com/api?api_key=";
$currency = 'ltc';
$table = "accounts_wemineltc";

require(__DIR__ . "/_mmcfe_pool.php");
