<?php

/**
 * Get current Blackcoin block number.
 * Using Blackchain.
 */

$currency = "bc1";
$block_table = "blackcoin_blocks";

$json = crypto_json_decode(crypto_get_contents(crypto_wrap_url(get_site_config("bc1_block_url"))));
// also available: difficulty, totalcoins, hashrate, reward

if (!isset($json['info']['blocks'])) {
	throw new ExternalAPIException("Block number was not present");
}
$block = $json['info']['blocks'];
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
