<?php

/**
 * Mintpal ticker job.
 */

$exchange_name = "mintpal";
$exchange_pairs = get_exchange_pairs();
$currencies = $exchange_pairs['mintpal'];

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://api.mintpal.com/v1/market/summary/")));

foreach ($currencies as $pair) {
	$found = false;
	foreach ($rates as $rate) {
		if ($rate['code'] == get_currency_abbr($pair[1]) && $rate['exchange'] == get_currency_abbr($pair[0])) {

			insert_new_ticker($job, $exchange, $pair[0], $pair[1], array(
				"last_trade" => $rate['last_price'],
				"bid" => $rate['top_bid'],
				"ask" => $rate['top_ask'],
				"volume" => $rate['24hvol'],
				// ignores 'yesterday_price', 'market_id', 'change', '24hhigh', '24hlow'
			));

			$found = true;
		}
	}

	if (!$found) {
		throw new ExternalAPIException("Pair " . $pair[0] . "/" . $pair[1] . " was not found");
	}

}
