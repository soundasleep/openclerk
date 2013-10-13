<?php

/**
 * A batch script to clean up ticker data and move it into the database as necessary.
 * This always executes (no job framework) so it should be used sparingly or as necessary.
 *
 * Arguments (in command line, use "-" for no argument):
 *   $key/1 required the automated key
 */

require(__DIR__ . "/inc/global.php");
require(__DIR__ . "/_batch.php");

require_batch_key();
batch_header("Batch cleanup ticker", "batch_cleanup_ticker");

crypto_log("Current time: " . date('r'));

// find all ticker data that needs to be inserted into the graph_data table
// TODO currently all database dates and PHP logic is based on server side timezone, not GMT/UTC
// database values need to all be modified to GMT before we can add '+00:00' for example
$cutoff_date = date('Y-m-d', strtotime(get_site_config('archive_ticker_data'))) . ' 23:59:59'; // +00:00';
$summary_date_prefix = " 00:00:00"; // +00:00
crypto_log("Cleaning up ticker data earlier than " . htmlspecialchars($cutoff_date) . " into summaries...");

$q = db()->prepare("SELECT * FROM ticker WHERE created_at <= :date ORDER BY created_at ASC");
$q->execute(array("date" => $cutoff_date));

// we're going to store this all in memory, because at least that way we don't have to
// execute logic twice
$stored = array();

$count = 0;
while ($ticker = $q->fetch()) {
	$count++;
	if ($count % 100 == 0) {
		crypto_log("Processed " . number_format($count) . "...");
	}

	$date = date('Y-m-d', strtotime($ticker['created_at'])) . $summary_date_prefix;
	$exchange = $ticker['exchange'];
	$cur1 = $ticker['currency1'];
	$cur2 = $ticker['currency2'];

	if (!isset($stored[$date])) {
		$stored[$date] = array();
	}
	if (!isset($stored[$date][$exchange])) {
		$stored[$date][$exchange] = array();
	}
	if (!isset($stored[$date][$exchange][$cur1])) {
		$stored[$date][$exchange][$cur1] = array();
	}
	if (!isset($stored[$date][$exchange][$cur1][$cur2])) {
		$stored[$date][$exchange][$cur1][$cur2] = array();
	}

	if (!isset($stored[$date][$exchange][$cur1][$cur2]['open'])) {
		$stored[$date][$exchange][$cur1][$cur2] = array(
			'min' => $ticker['last_trade'],
			'max' => $ticker['last_trade'],
			'open' => $ticker['last_trade'],
			'close' => $ticker['last_trade'],
			'volume' => $ticker['volume'],
			'samples' => 0,
			'values' => array(),
		);
	}

	// update as necessary
	$stored[$date][$exchange][$cur1][$cur2]['min'] = min($ticker['last_trade'], $stored[$date][$exchange][$cur1][$cur2]['min']);
	$stored[$date][$exchange][$cur1][$cur2]['max'] = max($ticker['last_trade'], $stored[$date][$exchange][$cur1][$cur2]['max']);
	$stored[$date][$exchange][$cur1][$cur2]['volume'] = max($ticker['volume'], $stored[$date][$exchange][$cur1][$cur2]['volume']);
	$stored[$date][$exchange][$cur1][$cur2]['close'] = $ticker['last_trade'];
	$stored[$date][$exchange][$cur1][$cur2]['samples']++;
	$stored[$date][$exchange][$cur1][$cur2]['buy'] = $ticker['buy']; // buy, sell are the last values for the day
	$stored[$date][$exchange][$cur1][$cur2]['sell'] = $ticker['sell'];
	$stored[$date][$exchange][$cur1][$cur2]['values'][] = $ticker['last_trade'];

}

crypto_log("Processed " . number_format($count) . " ticker entries");

// we now have lots of data; insert it
$insert_count = 0;
foreach ($stored as $date => $a) {
	foreach ($a as $exchange => $b) {
		foreach ($b as $cur1 => $c) {
			foreach ($c as $cur2 => $summary) {
				$q = db()->prepare("INSERT INTO graph_data_ticker SET
						exchange=:exchange, currency1=:currency1, currency2=:currency2, data_date=:data_date, samples=:samples,
						volume=:volume, last_trade_min=:min, last_trade_opening=:open, last_trade_closing=:close, last_trade_max=:max, buy=:buy, sell=:sell, last_trade_stdev=:stdev");
				$q->execute(array(
					'exchange' => $exchange,
					'currency1' => $cur1,
					'currency2' => $cur2,
					'data_date' => $date,
					'samples' => $summary['samples'],
					'volume' => $summary['volume'],
					'min' => $summary['min'],
					'open' => $summary['open'],
					'close' => $summary['close'],
					'max' => $summary['max'],
					'buy' => $summary['buy'],
					'sell' => $summary['sell'],
					'stdev' => stdev($summary['values']),
				));
				$insert_count++;
			}
		}
	}
}
crypto_log("Inserted " . number_format($insert_count) . " summarised entries into graph_data_ticker");

// finally, delete all the old data
// we've exhausted over everything so this should be safe
$q = db()->prepare("DELETE FROM ticker WHERE created_at <= :date ORDER BY created_at ASC");
$q->execute(array("date" => $cutoff_date));
crypto_log("Deleted " . number_format($count) . " ticker entries");

batch_footer();
