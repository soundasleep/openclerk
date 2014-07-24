<?php

/**
 * Crypto-Trade ticker job.
 */

$rates_list = array(
	array('usd', 'btc'),
	array('eur', 'btc'),
	array('usd', 'ltc'),
	array('eur', 'ltc'),
	array('btc', 'ltc'),
	array('usd', 'nmc'),
	array('btc', 'nmc'),
	array('usd', 'xpm'),
	array('btc', 'xpm'),
	array('ppc', 'xpm'),
	array('usd', 'ppc'),
	array('btc', 'ppc'),
	array('btc', 'trc'),
	array('usd', 'ftc'),
	array('btc', 'ftc'),
	array('btc', 'dvc'),
	array('btc', 'wdc'),
	array('btc', 'dgc'),
);

// tickers...
$first = true;
foreach ($rates_list as $rl) {
	$cur1 = $rl[0];
	$cur2 = $rl[1];
	$exchange_name = $exchange['name'];

	// sleep between requests
	if (!$first) {
		set_time_limit(30 + (get_site_config('sleep_crypto-trade_ticker') * 2));
		sleep(get_site_config('sleep_crypto-trade_ticker'));
	}
	$first = false;

	$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://crypto-trade.com/api/1/ticker/" . strtolower(get_currency_abbr($cur2)) . "_" . strtolower(get_currency_abbr($cur1)))));

	if (!isset($rates['data']['last'])) {
		if (isset($rates['error'])) {
			throw new ExternalAPIException("Could not find $cur1/$cur2 rate for $exchange_name: " . htmlspecialchars($rates['error']));
		}

		throw new ExternalAPIException("No $cur1/$cur2 rate for $exchange_name");
	}

	crypto_log(print_r($rates, true));
	insert_new_ticker($job, $exchange, strtolower($cur1), strtolower($cur2), array(
		"last_trade" => $rates['data']['last'],
		"bid" => $rates['data']['max_bid'],
		"ask" => $rates['data']['min_ask'],
		"volume" => $rates['data']['vol_' . get_currency_abbr($cur2)],
		// ignoring low, high
	));

}

// securities values are now calculated in securities_cryptotrade job
