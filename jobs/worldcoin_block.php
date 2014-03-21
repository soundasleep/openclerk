<?php

/**
 * Get current Worldcoin block number. Used to deduct unconfirmed transactions
 * when retrieving Worldcoin balances.
 */

$block = crypto_get_contents(crypto_wrap_url(get_site_config('wdc_block_url')));
if (!is_numeric($block) || !$block) {
	throw new ExternalAPIException("Worldcoin block number was not numeric: " . htmlspecialchars($block));
}

crypto_log("Current Worldcoin block number: " . number_format($block));

// disable old instances
$q = db()->prepare("UPDATE worldcoin_blocks SET is_recent=0 WHERE is_recent=1");
$q->execute();

// we have a balance; update the database
$q = db()->prepare("INSERT INTO worldcoin_blocks SET blockcount=:count,is_recent=1");
$q->execute(array(
	"count" => $block,
));
crypto_log("Inserted new worldcoin_blocks id=" . db()->lastInsertId());
