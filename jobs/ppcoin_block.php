<?php

/**
 * Get current PPCoin block number.
 * Using Blockr.io (Issue #240)
 */

$currency = "ppc";
$block_table = "ppcoin_blocks";

require(__DIR__ . "/_blockr_block.php");
