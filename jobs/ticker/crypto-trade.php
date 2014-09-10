<?php

/**
 * Crypto-Trade ticker job.
 */

$exchange_name = "crypto-trade";
$exchange_pairs = get_exchange_pairs();
$rates_list = $exchange_pairs['crypto-trade'];

// find any pairs we aren't finding
$expected = array();
foreach ($rates_list as $pair) {
	$expected[$pair[0] . $pair[1]] = $pair[0] . $pair[1];
}

$data = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://crypto-trade.com/api/1/tickers")));
foreach ($data['data'][0] as $key => $rate) {
	foreach ($rates_list as $pair) {
		$cur1 = $pair[0];
		$cur2 = $pair[1];
		if ($key == strtolower(get_currency_abbr($cur2)) . "_" . strtolower(get_currency_abbr($cur1))) {
			if (!isset($expected[$cur1 . $cur2])) {
				throw new ExternalAPIException("Found pair $cur1/$cur2 twice");
			}

			insert_new_ticker($job, $exchange, strtolower($cur1), strtolower($cur2), array(
				"last_trade" => $rate['last'],
				"bid" => $rate['max_bid'],
				"ask" => $rate['min_ask'],
				"volume" => $rate['vol_' . strtolower(get_currency_abbr($cur2))],
				// ignoring low, high
			));

			unset($expected[$cur1 . $cur2]);
		}
	}
}

if ($expected) {
	throw new ExternalAPIException("Did not find ticker pairs for " . implode(", ", $expected));
}

// securities values are now calculated in securities_cryptotrade job
