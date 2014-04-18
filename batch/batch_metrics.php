<?php

/**
 * A batch script to calculate performance metrics from data that has been collected.
 * This always executes (no job framework) so it should be used sparingly or as necessary.
 *
 * Arguments (in command line, use "-" for no argument):
 *   $key/1 required the automated key
 */

require(__DIR__ . "/../inc/global.php");
require(__DIR__ . "/_batch.php");

require_batch_key();
batch_header("Batch metrics", "batch_metrics");

crypto_log("Current time: " . date('r'));

{
	// "What database queries take the longest?"
	$report_type = "db_slow_queries";

	// select the worst ten queries
	$q = db_master()->prepare("SELECT query_id, SUM(query_count) AS qc, SUM(query_time) AS qt, MIN(page_id) AS pid FROM performance_metrics_slow_queries
			GROUP BY query_id ORDER BY SUM(query_time) / SUM(query_count) LIMIT 10");
	$q->execute();
	$data = $q->fetchAll();

	$q = db_master()->prepare("INSERT INTO performance_reports SET report_type=?");
	$q->execute(array($report_type));
	$report_id = db_master()->lastInsertId();

	foreach ($data as $row) {
		$q = db_master()->prepare("INSERT INTO performance_report_slow_queries SET report_id=?, query_id=?, query_count=?, query_time=?, page_id=?");
		$q->execute(array($report_id, $row['query_id'], $row['qc'], $row['qt'], $row['pid']));
	}

	crypto_log("Created report '$report_type'");
}

{
	// "What URLs take the longest to request?"

	$report_type = "curl_slow_urls";
	// select the worst ten urls
	$q = db_master()->prepare("SELECT url_id, SUM(url_count) AS qc, SUM(url_time) AS qt, MIN(page_id) AS pid FROM performance_metrics_slow_urls
			GROUP BY url_id ORDER BY SUM(url_time) / SUM(url_count) LIMIT 10");
	$q->execute();
	$data = $q->fetchAll();

	$q = db_master()->prepare("INSERT INTO performance_reports SET report_type=?");
	$q->execute(array($report_type));
	$report_id = db_master()->lastInsertId();

	foreach ($data as $row) {
		$q = db_master()->prepare("INSERT INTO performance_report_slow_urls SET report_id=?, url_id=?, url_count=?, url_time=?, page_id=?");
		$q->execute(array($report_id, $row['url_id'], $row['qc'], $row['qt'], $row['pid']));
	}

	crypto_log("Created report '$report_type'");
}

{
	// "What job types take the longest to execute?"
	// "What jobs spend the most time in PHP as opposed to the database?"

	$report_type = "jobs_slow";
	// select the worst ten urls
	$q = db_master()->prepare("SELECT job_type, SUM(time_taken) AS time_taken, COUNT(id) AS job_count,
			SUM(db_execute_time) + SUM(db_fetch_time) + SUM(db_fetch_all_time) AS database_time FROM performance_metrics_jobs
			GROUP BY job_type ORDER BY SUM(time_taken) / COUNT(id) LIMIT 20");
	$q->execute();
	$data = $q->fetchAll();

	$q = db_master()->prepare("INSERT INTO performance_reports SET report_type=?");
	$q->execute(array($report_type));
	$report_id = db_master()->lastInsertId();

	foreach ($data as $row) {
		$q = db_master()->prepare("INSERT INTO performance_report_slow_jobs SET report_id=?, job_type=?, job_time=?, job_count=?, job_database=?");
		$q->execute(array($report_id, $row['job_type'], $row['time_taken'], $row['job_count'], $row['database_time']));
	}

	crypto_log("Created report '$report_type'");
}

{
	// "What pages are taking the longest to load?"
	// "What pages spend the most time in PHP as opposed to the database?"

	$report_type = "pages_slow";
	// select the worst ten urls
	$q = db_master()->prepare("SELECT script_name, SUM(time_taken) AS time_taken, COUNT(id) AS page_count,
			SUM(db_execute_time) + SUM(db_fetch_time) + SUM(db_fetch_all_time) AS database_time FROM performance_metrics_pages
			GROUP BY script_name ORDER BY SUM(time_taken) / COUNT(id) LIMIT 20");
	$q->execute();
	$data = $q->fetchAll();

	$q = db_master()->prepare("INSERT INTO performance_reports SET report_type=?");
	$q->execute(array($report_type));
	$report_id = db_master()->lastInsertId();

	foreach ($data as $row) {
		$q = db_master()->prepare("INSERT INTO performance_report_slow_pages SET report_id=?, script_name=?, page_time=?, page_count=?, page_database=?");
		$q->execute(array($report_id, $row['script_name'], $row['time_taken'], $row['page_count'], $row['database_time']));
	}

	crypto_log("Created report '$report_type'");
}

{
	// "What graph types take the longest to render?"
	// "What graphs spend the most time in PHP as opposed to the database?"

	$report_type = "graphs_slow";
	// select the worst ten urls
	$q = db_master()->prepare("SELECT graph_type, SUM(time_taken) AS time_taken, COUNT(id) AS graph_count,
			SUM(db_execute_time) + SUM(db_fetch_time) + SUM(db_fetch_all_time) AS database_time FROM performance_metrics_graphs
			GROUP BY graph_type ORDER BY SUM(time_taken) / COUNT(id) LIMIT 20");
	$q->execute();
	$data = $q->fetchAll();

	$q = db_master()->prepare("INSERT INTO performance_reports SET report_type=?");
	$q->execute(array($report_type));
	$report_id = db_master()->lastInsertId();

	foreach ($data as $row) {
		$q = db_master()->prepare("INSERT INTO performance_report_slow_graphs SET report_id=?, graph_type=?, graph_time=?, graph_count=?, graph_database=?");
		$q->execute(array($report_id, $row['graph_type'], $row['time_taken'], $row['graph_count'], $row['database_time']));
	}

	crypto_log("Created report '$report_type'");
}

{
	// "How many ticker jobs are running per hour?"

	$report_type = "jobs_frequency";
	$q = db_master()->prepare("SELECT job_type, COUNT(id) AS job_count, MIN(created_at) AS start_time, MAX(created_at) AS end_time FROM performance_metrics_jobs
			WHERE job_type IN ('sum', 'ticker', 'blockchain', 'litecoin', 'namecoin', 'dogecoin', 'feathercoin')
			GROUP BY job_type");
	$q->execute();
	$data = $q->fetchAll();

	$q = db_master()->prepare("INSERT INTO performance_reports SET report_type=?");
	$q->execute(array($report_type));
	$report_id = db_master()->lastInsertId();

	foreach ($data as $row) {
		if ($row['job_count'] && strtotime($row['end_time']) != strtotime($row['start_time'])) {
			$q = db_master()->prepare("INSERT INTO performance_report_job_frequency SET report_id=?, job_type=?, job_count=?, jobs_per_hour=?");
			$q->execute(array($report_id, $row['job_type'], $row['job_count'],
					($row['job_count'] * 60 * 60) / (strtotime($row['end_time']) - strtotime($row['start_time']))));
		}
	}

	crypto_log("Created report '$report_type'");
}

// not implemented yet:
	// "What tables take the longest to query?"
	// "How long does it take for a page to be generated?"
	// "What pages have the most database queries?"
	// "What pages spend the most time in PHP as opposed to the database?"

	// "How many jobs are running per hour?"
	// "What jobs have the most database queries?"
	// "Which jobs time out the most?"
	// "How many blockchain requests fail?"
	// "What jobs take the longest requesting URLs?"

	// "How many jobs are being queued at once?"
	// "Which queue types take the longest?"

	// "What are the most common graph types?"
	// "How many ticker graphs are being requested?"


// we've processed all the data we want; delete old metrics data
$q = db_master()->prepare("DELETE FROM performance_metrics_slow_queries");
$q->execute();
$q = db_master()->prepare("DELETE FROM performance_metrics_slow_urls");
$q->execute();
$q = db_master()->prepare("DELETE FROM performance_metrics_repeated_queries");
$q->execute();
$q = db_master()->prepare("DELETE FROM performance_metrics_repeated_urls");
$q->execute();
$q = db_master()->prepare("DELETE FROM performance_metrics_pages");
$q->execute();
$q = db_master()->prepare("DELETE FROM performance_metrics_graphs");
$q->execute();
$q = db_master()->prepare("DELETE FROM performance_metrics_jobs");
$q->execute();
$q = db_master()->prepare("DELETE FROM performance_metrics_queues");
$q->execute();

crypto_log("Deleted old metric data.");

batch_footer();
