<?php

/**
 * PPCoin Search job (PPC).
 * Uses blockr.io API (issue #240).
 * Blockr.io API supports confirmations without having to rely on block count!
 */

$currency = "ppc";
$confirmations = get_site_config('ppc_confirmations');

require(__DIR__ . "/_blockr.php");
