<?php

/**
 * A batch script to clean up balances data and move it into the database as necessary.
 * This always executes (no job framework) so it should be used sparingly or as necessary.
 *
 * Arguments (in command line, use "-" for no argument):
 *   $key/1 required the automated key
 */

require(__DIR__ . "/inc/global.php");
require(__DIR__ . "/_batch.php");

require_batch_key();
batch_header("Batch cleanup balances", "batch_cleanup_balances");

crypto_log("Current time: " . date('r'));

// find all ticker data that needs to be inserted into the graph_data table
// TODO currently all database dates and PHP logic is based on server side timezone, not GMT/UTC
// database values need to all be modified to GMT before we can add '+00:00' for example
$cutoff_date = date('Y-m-d', strtotime(get_site_config('archive_balances_data'))) . ' 23:59:59'; // +00:00';
$summary_date_prefix = " 00:00:00"; // +00:00
crypto_log("Cleaning up balances data earlier than " . htmlspecialchars($cutoff_date) . " into summaries...");

$q = db()->prepare("SELECT * FROM balances WHERE created_at <= :date ORDER BY created_at ASC");
$q->execute(array("date" => $cutoff_date));

// we're going to store this all in memory, because at least that way we don't have to
// execute logic twice
$stored = array();

$count = 0;
while ($balance = $q->fetch()) {
	$count++;
	if ($count % 100 == 0) {
		crypto_log("Processed " . number_format($count) . "...");
	}

	$date = date('Y-m-d', strtotime($balance['created_at'])) . $summary_date_prefix;
	$user_id = $balance['user_id'];
	$exchange = $balance['exchange'];
	$account_id = $balance['account_id'];
	$currency = $balance['currency'];

	if (!isset($stored[$date])) {
		$stored[$date] = array();
	}
	if (!isset($stored[$date][$user_id])) {
		$stored[$date][$user_id] = array();
	}
	if (!isset($stored[$date][$user_id][$exchange])) {
		$stored[$date][$user_id][$exchange] = array();
	}
	if (!isset($stored[$date][$user_id][$exchange][$account_id])) {
		$stored[$date][$user_id][$exchange][$account_id] = array();
	}

	if (!isset($stored[$date][$user_id][$exchange][$account_id][$currency]['open'])) {
		$stored[$date][$user_id][$exchange][$account_id][$currency] = array(
			'min' => $balance['balance'],
			'max' => $balance['balance'],
			'open' => $balance['balance'],
			'close' => $balance['balance'],
			'samples' => 0,
			'values' => array(),
		);
	}

	// update as necessary
	$stored[$date][$user_id][$exchange][$account_id][$currency]['min'] = min($balance['balance'], $stored[$date][$user_id][$exchange][$account_id][$currency]['min']);
	$stored[$date][$user_id][$exchange][$account_id][$currency]['max'] = max($balance['balance'], $stored[$date][$user_id][$exchange][$account_id][$currency]['max']);
	$stored[$date][$user_id][$exchange][$account_id][$currency]['close'] = $balance['balance'];
	$stored[$date][$user_id][$exchange][$account_id][$currency]['samples']++;
	$stored[$date][$user_id][$exchange][$account_id][$currency]['values'][] = $balance['balance'];

}

crypto_log("Processed " . number_format($count) . " balances entries");

// we now have lots of data; insert it
// danger! danger! five nested loops!
$insert_count = 0;
foreach ($stored as $date => $a) {
	foreach ($a as $user_id => $b) {
		foreach ($b as $exchange => $c) {
			foreach ($c as $account_id => $d) {
				foreach ($d as $currency => $summary) {
					$q = db()->prepare("INSERT INTO graph_data_balances SET
							user_id=:user_id, exchange=:exchange, account_id=:account_id, currency=:currency, data_date=:data_date, samples=:samples,
							balance_min=:min, balance_opening=:open, balance_closing=:close, balance_max=:max, balance_stdev=:stdev");
					$q->execute(array(
						'user_id' => $user_id,
						'exchange' => $exchange,
						'account_id' => $account_id,
						'currency' => $currency,
						'data_date' => $date,
						'samples' => $summary['samples'],
						'min' => $summary['min'],
						'open' => $summary['open'],
						'close' => $summary['close'],
						'max' => $summary['max'],
						'stdev' => stdev($summary['values']),
					));
					$insert_count++;
				}
			}
		}
	}
}
crypto_log("Inserted " . number_format($insert_count) . " balances entries into graph_data_balances");

// finally, delete all the old data
// we've exhausted over everything so this should be safe
$q = db()->prepare("DELETE FROM balances WHERE created_at <= :date ORDER BY created_at ASC");
$q->execute(array("date" => $cutoff_date));
crypto_log("Deleted " . number_format($count) . " summary entries");

batch_footer();
