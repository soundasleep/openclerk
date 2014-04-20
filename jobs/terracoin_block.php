<?php

/**
 * Get current Terracoin block number.
 * Since this isn't based off Abe, we scrape HTML instead (ergh).
 */

$currency = "trc";
$block_table = "terracoin_blocks";
require(__DIR__ . "/_cryptocoinexplorer_block.php");
