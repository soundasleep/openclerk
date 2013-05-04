<?php

/**
 * WeMineLTC balance job.
 */

$exchange = "wemineltc";
$url = "http://www.wemineltc.com/api?api_key=";
$currency = 'ltc';
$table = "accounts_wemineltc";

require("_mmcfe_pool.php");
