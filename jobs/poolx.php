<?php

/**
 * Pool-X.eu balance job.
 */

$exchange = "poolx";
$url = "http://pool-x.eu/api?api_key=";
$currency = 'ltc';
$table = "accounts_poolx";

require("_mmcfe_pool.php");
