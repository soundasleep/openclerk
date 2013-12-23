<?php

/**
 * Coins-E ticker job.
 */

$rates_list = array(
	// not all coins are tracked
	array('btc', 'xpm'),
	array('btc', 'trc'),
	array('btc', 'wdc'),
	array('btc', 'dog'),
	array('btc', 'ftc'),
	array('btc', 'ltc'),
	array('btc', 'ppc'),

	array('ltc', 'xpm'),

	array('xpm', 'ppc'),
);

// get the ticker
$rates = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://www.coins-e.com/api/v2/markets/data/")));
foreach ($rates_list as $rl) {
	$cur1 = $rl[0];
	$cur2 = $rl[1];
	$exchange_name = $exchange['name'];

	$key = strtoupper(get_currency_abbr($cur2) . "_" . get_currency_abbr($cur1));

	if (!isset($rates['markets'][$key]['marketstat']['ltp'])) {
		throw new ExternalAPIException("No $cur1/$cur2 rate for $exchange_name");
	}

	$last = $rates['markets'][$key]['marketstat']['ltp'];
	$bid = $rates['markets'][$key]['marketstat']['bid'];
	$ask = $rates['markets'][$key]['marketstat']['ask'];
	$volume = $rates['markets'][$key]['marketstat']['24h']['volume'];

	crypto_log("$exchange_name rate for $cur1/$cur2: $last ($bid/$ask)");

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
		"last_trade" => $last,
		"buy" => $bid,
		"sell" => $ask,
		"volume" => $volume,
		// ignoring 24h low, high, average rate etc
		"job_id" => $job['id'],
	));

	crypto_log("Inserted new ticker id=" . db()->lastInsertId());

}
