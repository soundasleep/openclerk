<?php

/**
 * Get current Namecoin block number. Used to deduct unconfirmed transactions
 * when retrieving Namecoin balances.
 */

$block = crypto_get_contents(crypto_wrap_url(get_site_config('nmc_block_url')));
if (!is_numeric($block) || !$block) {
	throw new ExternalAPIException("Namecoin block number was not numeric: " . htmlspecialchars($block));
}

crypto_log("Current Namecoin block number: " . number_format($block));

// disable old instances
$q = db()->prepare("UPDATE namecoin_blocks SET is_recent=0 WHERE is_recent=1");
$q->execute();

// we have a balance; update the database
$q = db()->prepare("INSERT INTO namecoin_blocks SET blockcount=:count,is_recent=1");
$q->execute(array(
	"count" => $block,
));
crypto_log("Inserted new namecoin_blocks id=" . db()->lastInsertId());
