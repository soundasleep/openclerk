<?php

/**
 * Get current Worldcoin block number.
 * Since this isn't based off Abe, we scrape HTML instead (ergh).
 */

$currency = "wdc";
$block_table = "worldcoin_blocks";
require(__DIR__ . "/_cryptocoinexplorer_block.php");
