<?php

/**
 * Mining Foreman FTC balance job.
 */

$exchange = "miningforeman_ftc";
$url = "http://ftc.mining-foreman.org/api?api_key=";
$currency = 'ftc';
$table = "accounts_miningforeman_ftc";

require("_mmcfe_pool.php");
