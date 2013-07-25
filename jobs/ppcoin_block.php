<?php

/**
 * Get current PPCoin block number. Used to deduct unconfirmed transactions
 * when retrieving Feathercoin balances.
 */

$block = crypto_get_contents(crypto_wrap_url("http://ppc.cryptocoinexplorer.com/chain/PPCoin/q/getblockcount"));
if (!is_numeric($block) || !$block) {
	throw new ExternalAPIException("PPCoin block number was not numeric: " . htmlspecialchars($block));
}

crypto_log("Current PPCoin block number: " . number_format($block));

// disable old instances
$q = db()->prepare("UPDATE ppcoin_blocks SET is_recent=0 WHERE is_recent=1");
$q->execute();

// we have a balance; update the database
$q = db()->prepare("INSERT INTO ppcoin_blocks SET blockcount=:count,is_recent=1");
$q->execute(array(
	"count" => $block,
));
crypto_log("Inserted new ppcoin_blocks id=" . db()->lastInsertId());
