<?php

/**
 * Mine-Litecoin balance job.
 */

$exchange = "mine-litecoin";
$url = "https://www.mine-litecoin.com/api.php?api_key=";
$currency = 'ltc';
$table = "accounts_mine_litecoin";

require("_mmcfe_pool.php");
