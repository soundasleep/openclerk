<?php

/**
 * Blockr.io is awesome.
 */

$json = crypto_json_decode(crypto_get_contents(crypto_wrap_url(get_site_config($currency . '_block_url'))));

// JSend standard
// throws an ExternalAPIException if something bad happened
// otherwise returns the data
$data = crypto_jsend($json);

if (!isset($data['nb'])) {
	throw new ExternalAPIException("No block number 'nb' found");
}
$block = $data['nb'];

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
