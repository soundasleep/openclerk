<?php

/**
 * A batch script to clean up summary data and move it into the database as necessary.
 * This always executes (no job framework) so it should be used sparingly or as necessary.
 *
 * Arguments (in command line, use "-" for no argument):
 *   $key/1 required the automated key
 */

define('USE_MASTER_DB', true);		// always use the master database for selects!

require(__DIR__ . "/../inc/global.php");
require(__DIR__ . "/_batch.php");

require_batch_key();
batch_header("Batch cleanup summary", "batch_cleanup_summary");

crypto_log("Current time: " . date('r'));

// find all ticker data that needs to be inserted into the graph_data table
// TODO currently all database dates and PHP logic is based on server side timezone, not GMT/UTC
// database values need to all be modified to GMT before we can add '+00:00' for example
$cutoff_date = date('Y-m-d', strtotime(get_site_config('archive_summary_data'))) . ' 23:59:59'; // +00:00';
$summary_date_prefix = " 00:00:00"; // +00:00
crypto_log("Cleaning up ticker data earlier than " . htmlspecialchars($cutoff_date) . " into summaries...");

$q = db_master()->prepare("SELECT * FROM summary_instances WHERE created_at <= :date ORDER BY created_at ASC");
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
	$user_id = $ticker['user_id'];
	$type = $ticker['summary_type'];

	if (!isset($stored[$date])) {
		$stored[$date] = array();
	}
	if (!isset($stored[$date][$user_id])) {
		$stored[$date][$user_id] = array();
	}
	if (!isset($stored[$date][$user_id][$type])) {
		$stored[$date][$user_id][$type] = array();
	}

	if (!isset($stored[$date][$user_id][$type]['open'])) {
		$stored[$date][$user_id][$type] = array(
			'min' => $ticker['balance'],
			'max' => $ticker['balance'],
			'open' => $ticker['balance'],
			'close' => $ticker['balance'],
			'samples' => 0,
			'values' => array(),
		);
	}

	// update as necessary
	$stored[$date][$user_id][$type]['min'] = min($ticker['balance'], $stored[$date][$user_id][$type]['min']);
	$stored[$date][$user_id][$type]['max'] = max($ticker['balance'], $stored[$date][$user_id][$type]['max']);
	$stored[$date][$user_id][$type]['close'] = $ticker['balance'];
	$stored[$date][$user_id][$type]['samples']++;
	$stored[$date][$user_id][$type]['values'][] = $ticker['balance'];

}

crypto_log("Processed " . number_format($count) . " summary entries");

// we now have lots of data; insert it
$insert_count = 0;
foreach ($stored as $date => $a) {
	foreach ($a as $user_id => $b) {
		foreach ($b as $type => $summary) {
			$q = db_master()->prepare("INSERT INTO graph_data_summary SET
					user_id=:user_id, summary_type=:summary_type, data_date=:data_date, samples=:samples,
					balance_min=:min, balance_opening=:open, balance_closing=:close, balance_max=:max, balance_stdev=:stdev");
			$q->execute(array(
				'user_id' => $user_id,
				'summary_type' => $type,
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
crypto_log("Inserted " . number_format($insert_count) . " summary entries into graph_data_summary");

// finally, delete all the old data
// we've exhausted over everything so this should be safe
$q = db_master()->prepare("DELETE FROM summary_instances WHERE created_at <= :date ORDER BY created_at ASC");
$q->execute(array("date" => $cutoff_date));
crypto_log("Deleted " . number_format($count) . " summary entries");

batch_footer();
