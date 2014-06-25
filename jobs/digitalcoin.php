<?php

/**
 * Get Digitalcoin balance (DGC).
 * Uses blockr.io API (issue #240).
 * Blockr.io API supports confirmations without having to rely on block count!
 */

$currency = "dgc";
$confirmations = get_site_config('dgc_confirmations');

require(__DIR__ . "/_blockr.php");
