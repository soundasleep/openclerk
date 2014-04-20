<?php

/**
 * Get current Novacoin block number.
 * Since this isn't based off Abe, we scrape HTML instead (ergh).
 */

$currency = "nvc";
$block_table = "novacoin_blocks";
require(__DIR__ . "/_cryptocoinexplorer_block.php");
