<?php

/**
 * Get current Novacoin block number.
 * Since this isn't based off Abe, we scrape HTML instead (ergh).
 */

$currency = "nvc";
$block_table = "novacoin_blocks";

$html = crypto_get_contents(crypto_wrap_url(get_site_config('nvc_block_url_html')));

// look for the first block number (this is dreadful)
$matches = false;
if (preg_match("#focus\\(\\);['\"]><td>([0-9]+)</td>#im", $html, $matches)) {
	$block = $matches[1];
} else {
	throw new ExternalAPIException("Could not find current block number on page");
}

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

