<?php

/**
 * Viacoin (VIA) Block job.
 */

$raw = crypto_get_contents(crypto_wrap_url(get_site_config('via_block_url')));
$data = crypto_json_decode($raw);

if (!isset($data['info']['blocks'])) {
	throw new ExternalAPIException("Could not find latest block number");
}
$block = $data['info']['blocks'];

crypto_log("Current Viacoin block number: " . number_format($block));

// disable old instances
$q = db()->prepare("UPDATE viacoin_blocks SET is_recent=0 WHERE is_recent=1");
$q->execute();

// we have a balance; update the database
$q = db()->prepare("INSERT INTO viacoin_blocks SET blockcount=:count,is_recent=1");
$q->execute(array(
	"count" => $block,
));
crypto_log("Inserted new viacoin_blocks id=" . db()->lastInsertId());
