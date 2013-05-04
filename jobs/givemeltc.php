<?php

/**
 * Give Me LTC balance job.
 */

$exchange = "givemeltc";
$url = "https://give-me-ltc.com/api?api_key=";
$currency = 'ltc';
$table = "accounts_givemeltc";

require("_mmcfe_pool.php");
