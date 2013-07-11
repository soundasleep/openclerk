<?php

/**
 * Batch script: update external APIs status.
 *
 * Arguments (in command line, use "-" for no argument):
 *   $key/1 required the automated key
 */

require("inc/global.php");
require("_batch.php");

require_batch_key();
batch_header("Batch external status", "batch_external");

crypto_log("Current time: " . date('r'));

// we just summarise the data from the last X jobs
// (rather than the last X minutes, which would require a sort of all jobs)
// because 'ticker' jobs are broken down into exchanges, we also want to get each individual exchange data
$summary = array();
$queries = array(
	"SELECT jobs.* FROM jobs
		WHERE jobs.id > (SELECT MAX(jobs.id) FROM jobs WHERE is_executed=1) - 10000 AND is_executed=1 AND job_type <> 'ticker'",
	"SELECT jobs.*, exchanges.name AS exchange FROM jobs
		JOIN exchanges ON jobs.arg_id=exchanges.id
		WHERE jobs.id > (SELECT MAX(jobs.id) FROM jobs WHERE is_executed=1) - 10000 AND is_executed=1 AND job_type = 'ticker'",
);
$sample_size = 0;
foreach ($queries as $query) {
	$q = db()->prepare($query);
	$q->execute();
	while ($job = $q->fetch()) {
		$job_type = $job['job_type'];
		if (isset($job['exchange'])) {
			$job_type .= "_" . $job['exchange'];
		}
		if (!isset($summary[$job_type])) {
			$summary[$job_type] = array('count' => 0, 'errors' => 0, 'first' => $job['executed_at'], 'last' => $job['executed_at']);
		}
		$summary[$job_type]['count']++;
		if ($job['is_error']) {
			$summary[$job_type]['errors']++;
		}
		$summary[$job_type]['last'] = $job['executed_at'];
		$sample_size++;
	}
}

crypto_log(print_r($summary, true));

// update the database

// delete very old updates
$q = db()->prepare("DELETE FROM external_status WHERE DATE_ADD(created_at, INTERVAL 30 DAY) < NOW()");
$q->execute();

// other statuses are marked as old
$q = db()->prepare("UPDATE external_status SET is_recent=0");
$q->execute();

foreach ($summary as $key => $data) {
	$q = db()->prepare("INSERT INTO external_status SET is_recent=1, job_type=:key, job_count=:count, job_errors=:errors, job_first=:first, job_last=:last, sample_size=:sample_size");
	$q->execute(array(
		"key" => $key,
		"count" => $data['count'],
		"errors" => $data['errors'],
		"first" => db_date($data['first']),
		"last" => db_date($data['last']),
		"sample_size" => $sample_size,
	));

	// TODO add is_daily_data flag, summarise data through cleanup, etc

}

crypto_log("Complete from " . number_format($sample_size) . " job samples into " . number_format(count($summary)) . " summary values.");

batch_footer();
