<?php

/**
 * Batch script: update external APIs status.
 *
 * Arguments (in command line, use "-" for no argument):
 *   $key/1 required the automated key
 */

require("inc/global.php");

if (!(isset($argv) && $argv[1] == get_site_config("automated_key")) && require_get("key") != get_site_config("automated_key"))
	throw new Exception("Invalid key");

if (require_get("key", false)) {
	// we're running from a web browser
	require("layout/templates.php");
	$options = array();
	if (require_get("refresh", false)) {
		$options["refresh"] = require_get("refresh");
	}
	page_header("External Status", "page_batch_external", $options);
}

crypto_log("Current time: " . date('r'));

// we just summarise the data from the last X jobs
// (rather than the last X minutes, which would require a sort of all jobs)
// because 'ticker' jobs are broken down into exchanges, we also want to get each individual exchange data
$summary = array();
$queries = array(
	"SELECT jobs.* FROM jobs
		WHERE jobs.id > (SELECT MAX(jobs.id) FROM jobs WHERE is_executed=1) - 5000 AND is_executed=1 AND job_type <> 'ticker'",
	"SELECT jobs.*, exchanges.name AS exchange FROM jobs
		JOIN exchanges ON jobs.arg_id=exchanges.id
		WHERE jobs.id > (SELECT MAX(jobs.id) FROM jobs WHERE is_executed=1) - 5000 AND is_executed=1 AND job_type = 'ticker'",
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

echo "\n<li>" . print_r($summary, true) . "</li>";

// update the database
$q = db()->prepare("DELETE FROM external_status");
$q->execute();

foreach ($summary as $key => $data) {
	$q = db()->prepare("INSERT INTO external_status SET job_type=:key, job_count=:count, job_errors=:errors, job_first=:first, job_last=:last, sample_size=:sample_size");
	$q->execute(array(
		"key" => $key,
		"count" => $data['count'],
		"errors" => $data['errors'],
		"first" => db_date($data['first']),
		"last" => db_date($data['last']),
		"sample_size" => $sample_size,
	));
}

echo "\n<li>Complete from " . number_format($sample_size) . " job samples.";

if (require_get("key", false)) {
	// we're running from a web browser
	// include page gen times etc
	page_footer();
}
