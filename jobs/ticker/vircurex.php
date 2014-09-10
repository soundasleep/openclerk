<?php

/**
 * Vircurex ticker job.
 */

$exchange_name = "vircurex";
$exchange_pairs = get_exchange_pairs();
$rates_list = $exchange_pairs['vircurex'];

$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://api.vircurex.com/api/get_info_for_currency.json")));

foreach ($rates_list as $rl) {
	$cur1 = $rl[0];
	$cur2 = $rl[1];

	if (!isset($rates[get_currency_abbr($cur2)][get_currency_abbr($cur1)])) {
		throw new ExternalAPIException("No " . $cur1 . "/" . $cur2 . " rate for Vircurex");
	}

	$obj = $rates[get_currency_abbr($cur2)][get_currency_abbr($cur1)];

	insert_new_ticker($job, $exchange, strtolower($cur1), strtolower($cur2), array(
		"last_trade" => $obj['last_trade'],
		"bid" => $obj['highest_bid'],
		"ask" => $obj['lowest_ask'],
		"volume" => $obj['volume'],
	));

}
