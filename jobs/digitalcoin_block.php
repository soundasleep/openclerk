<?php

/**
 * Get current Digitalcoin block number.
 * Since this isn't based off Abe, we scrape HTML instead (ergh).
 */

$currency = "dgc";
$block_table = "digitalcoin_blocks";
require(__DIR__ . "/_cryptocoinexplorer_block.php");
