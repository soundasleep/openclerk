<?php

/**
 * Vault of Satoshi reported currencies job (#121).
 * Vault of Satoshi provides /info/currency which requires an authenticated endpoint.
 */

require(__DIR__ . "/../_vaultofsatoshi.php");

$data = vaultofsatoshi_query(get_site_config('vaultofsatoshi_info_currency_api_key'), get_site_config('vaultofsatoshi_info_currency_api_secret'), "/info/currency");

if (isset($data['message']) && $data['message']) {
	throw new ExternalAPIException(htmlspecialchars($balance['message']));
}

// update the database
$q = db()->prepare("DELETE FROM reported_currencies WHERE exchange=?");
$q->execute(array($exchange['name']));

foreach ($data['data'] as $row) {
	$currency = strtolower($row['code']);
	crypto_log("Found currency $currency");

	if (!$row['tradeable']) {
		crypto_log("Currency is not tradeable: bailing");
		continue;
	}

	$q = db()->prepare("INSERT INTO reported_currencies SET exchange=?, currency=?");
	$q->execute(array($exchange['name'], $currency));
}
