<?php

/**
 * Get current Worldcoin block number.
 * Using http://www.worldcoinexplorer.com (Issue #238)
 */

$currency = "wdc";
$block_table = "worldcoin_blocks";

$json = crypto_json_decode(crypto_get_contents(crypto_wrap_url(get_site_config("wdc_block_url"))));
// also available: difficulty, totalcoins, hashrate, reward

if (!isset($json['Blocks'])) {
	throw new ExternalAPIException("Block number was not present");
}
$block = $json['Blocks'];
crypto_log("Current $currency block number: " . number_format($block));

// disable old instances
$q = db()->prepare("UPDATE $block_table SET is_recent=0 WHERE is_recent=1");
$q->execute();

// we have a balance; update the database
$q = db()->prepare("INSERT INTO $block_table SET blockcount=:count,is_recent=1");
$q->execute(array(
	"count" => $block,
));
crypto_log("Inserted new $block_table id=" . db()->lastInsertId());
