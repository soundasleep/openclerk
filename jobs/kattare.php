<?php

/**
 * ltc.kattare.com balance job.
 */

$exchange = "kattare";
$url = "http://ltc.kattare.com/api.php?api_key=";
$currency = 'ltc';
$table = "accounts_kattare";

require(__DIR__ . "/_mmcfe_pool.php");
