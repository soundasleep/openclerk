<?php

/**
 * WeMineFTC balance job.
 */

$exchange = "wemineltc";
$url = "https://www.wemineftc.com/api?api_key=";
$currency = 'ftc';
$table = "accounts_wemineftc";

require(__DIR__ . "/_mmcfe_pool.php");
