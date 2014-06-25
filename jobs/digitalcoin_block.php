<?php

/**
 * Get current Digitalcoin block number.
 * Using Blockr.io (Issue #240)
 */

$currency = "dgc";
$block_table = "digitalcoin_blocks";

require(__DIR__ . "/_blockr_block.php");
