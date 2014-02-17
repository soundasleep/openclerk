<?php

/**
 * Ecoining Peercoin pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "ecoining_ppc";
$currency = 'ppc';
$table = "accounts_ecoining_ppc";
$api_url = "https://peercoin.ecoining.com/index.php?page=api&";

require(__DIR__ . "/_mpos_pool.php");
