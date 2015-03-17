<?php

/**
 * WeMineFTC balance job.
 */

$exchange = "wemineftc";
$url = "http://www.wemineftc.com/api?api_key=";
$currency = 'ftc';
$table = "accounts_wemineftc";

require(__DIR__ . "/_mmcfe_pool.php");
