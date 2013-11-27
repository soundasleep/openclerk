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

	$rates = json_decode(crypto_get_contents(crypto_wrap_url("https://crypto-trade.com/api/1/ticker/" . $cur2 . "_" . $cur1)), true);
	if ($rates === null) {
		throw new ExternalAPIException("Invalid JSON detected");
	}

	if (!isset($rates['data']['last'])) {
		if (isset($rates['error'])) {
			throw new ExternalAPIException("Could not find $cur1/$cur2 rate for $exchange_name: " . htmlspecialchars($rates['error']));
		}

		throw new ExternalAPIException("No $cur1/$cur2 rate for $exchange_name");
	}

	crypto_log("$exchange_name rate for $cur1/$cur2: " . $rates['data']['last'] . " (" . $rates['data']['max_bid'] . " / " . $rates['data']['min_ask'] . ")");

	// update old recent values
	$q = db()->prepare("UPDATE ticker SET is_recent=0 WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => strtolower($cur1),
		"currency2" => strtolower($cur2),
	));

	// all other data from today is now old
	// NOTE if the system time changes between the next two commands, then we may erraneously
	// specify that there is no valid daily data. one solution is to specify NOW() as $created_at rather than
	// relying on MySQL
	$q = db()->prepare("UPDATE ticker SET is_daily_data=0 WHERE is_daily_data=1 AND exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND
		date_format(created_at, '%d-%m-%Y') = date_format(now(), '%d-%m-%Y')");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => strtolower($cur1),
		"currency2" => strtolower($cur2),
	));

	// insert in new ticker value
	$q = db()->prepare("INSERT INTO ticker SET is_recent=1, exchange=:exchange, currency1=:currency1, currency2=:currency2, last_trade=:last_trade, buy=:buy, sell=:sell, volume=:volume, job_id=:job_id, is_daily_data=1");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => strtolower($cur1),
		"currency2" => strtolower($cur2),
		"last_trade" => $rates['data']['last'],
		"buy" => $rates['data']['max_bid'],
		"sell" => $rates['data']['min_ask'],
		"volume" => $rates['data']['vol_' . $cur2], // e.g. btc_usd will use vol_btc rather than vol_usd
		// ignoring low, high
		"job_id" => $job['id'],
	));

	crypto_log("Inserted new ticker id=" . db()->lastInsertId());
}

// ...and securities
// all existing security values are no longer recent
$q = db()->prepare("UPDATE balances SET is_recent=0 WHERE exchange=?");
$q->execute(array("securities_crypto-trade"));

$q = db()->prepare("SELECT * FROM securities_cryptotrade");
$q->execute();
$securities = $q->fetchAll();
foreach ($securities as $sec) {

	$cur1 = $sec['currency'];
	$cur2 = strtolower($sec['name']);
	$exchange_name = $exchange['name'];

	// sleep between requests
	if (!$first) {
		set_time_limit(30 + (get_site_config('sleep_crypto-trade_ticker') * 2));
		sleep(get_site_config('sleep_crypto-trade_ticker'));
	}
	$first = false;

	$rates = json_decode(crypto_get_contents(crypto_wrap_url("https://crypto-trade.com/api/1/ticker/" . $cur2 . "_" . $cur1)), true);
	if ($rates === null) {
		throw new ExternalAPIException("Invalid JSON detected");
	}

	if (!isset($rates['data']['max_bid'])) {
		if (isset($rates['error'])) {
			throw new ExternalAPIException("Could not find $cur1/$cur2 rate for $exchange_name: " . htmlspecialchars($rates['error']));
		}

		throw new ExternalAPIException("No $cur1/$cur2 rate for $exchange_name");
	}

	// insert new balance
	insert_new_balance($job, $sec, 'securities_crypto-trade', $sec['currency'], $rates['data']['max_bid']);

}
