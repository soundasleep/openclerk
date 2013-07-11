<?php

/**
 * Mining Foreman balance job.
 */

$exchange = "miningforeman";
$url = "http://www.mining-foreman.org/api?api_key=";
$currency = 'ltc';
$table = "accounts_miningforeman";

require("_mmcfe_pool.php");
