<?php
/**
 * Performance metrics functionality.
 */

function performance_metrics_enabled() {
	return get_site_config('performance_metrics_enabled');
}

$_performance_metrics = array();

/**
 * Called when the page starts.
 */
function performance_metrics_page_start() {
	if (!performance_metrics_enabled()) {
		return;
	}
	global $_performance_metrics;
	$_performance_metrics['page_start'] = microtime(true);
}

/**
 * Called when the page is complete.
 */
function performance_metrics_page_end() {
	if (!performance_metrics_enabled()) {
		return;
	}
	global $_performance_metrics;
	$page_time = microtime(true) - $_performance_metrics['page_start'];

	// "What database queries take the longest?"
	// "What tables take the longest to query?"
	// "What URLs take the longest to request?"
	// "How long does it take for a page to be generated?"
	// "What pages are taking the longest to load?"
	// "What pages have the most database queries?"
	// "What pages spend the most time in PHP as opposed to the database?"
	$query = "INSERT INTO performance_metrics_pages SET script_name=:script_name, time_taken=:time_taken, is_logged_in=:is_logged_in";
	$args = array(
		'script_name' => isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null,
		'time_taken' => $page_time * 1000,
		'is_logged_in' => user_logged_in() ? 1 : 0,
	);

	list($query, $args) = prepare_timed_data($query, $args);

	$q = db()->prepare($query);
	$q->execute($args);
	$page_id = db()->lastInsertId();

	// we also want to keep track of the slowest queries and URLs here
	if (get_site_config('timed_sql')) {
		global $global_timed_sql;
		foreach ($global_timed_sql['queries'] as $query => $data) {
			// only if it's over a specified limit, so we don't spam the database with super fast queries
			$slow_query = ($data['time'] / $data['count']) > get_site_config('performance_metrics_slow_query');
			$repeated_query = $data['count'] > get_site_config('performance_metrics_repeated_query');
			if ($slow_query || $repeated_query) {

				$query_substr = substr($query, 0, 255);
				$q = db()->prepare("SELECT id FROM performance_metrics_queries WHERE query=? LIMIT 1");
				$q->execute(array($query_substr));
				$pq = $q->fetch();
				if (!$pq) {
					$q = db()->prepare("INSERT INTO performance_metrics_queries SET query=?");
					$q->execute(array($query_substr));
					$pq = array('id' => db()->lastInsertId());
				}

				if ($slow_query) {
					$q = db()->prepare("INSERT INTO performance_metrics_slow_queries SET query_id=?, query_count=?, query_time=?, page_id=?");
					$q->execute(array($pq['id'], $data['count'], $data['time'], $page_id));
				}
				if ($repeated_query) {
					$q = db()->prepare("INSERT INTO performance_metrics_repeated_queries SET query_id=?, query_count=?, query_time=?, page_id=?");
					$q->execute(array($pq['id'], $data['count'], $data['time'], $page_id));
				}

			}
		}
	}

	if (get_site_config('timed_curl')) {
		global $global_timed_curl;
		if (isset($global_timed_curl)) {
			foreach ($global_timed_curl['urls'] as $url => $data) {
				// only if it's over a specified limit, so we don't spam the database with super fast URLs
				$slow_url = ($data['time'] / $data['count']) > get_site_config('performance_metrics_slow_curl');
				$repeated_url = $data['count'] > get_site_config('performance_metrics_repeated_curl');
				if ($slow_url || $repeated_url) {

					$url_substr = substr($url, 0, 255);
					$q = db()->prepare("SELECT id FROM performance_metrics_urls WHERE query=? LIMIT 1");
					$q->execute(array($url_substr));
					$pq = $q->fetch();
					if (!$pq) {
						$q = db()->prepare("INSERT INTO performance_metrics_urls SET query=?");
						$q->execute(array($url_substr));
						$pq = array('id' => db()->lastInsertId());
					}

					if ($slow_url) {
						$q = db()->prepare("INSERT INTO performance_metrics_slow_urls SET query_id=?, query_count=?, query_time=?, page_id=?");
						$q->execute(array($pq['id'], $data['count'], $data['time'], $page_id));
					}
					if ($repeated_url) {
						$q = db()->prepare("INSERT INTO performance_metrics_repeated_urls SET query_id=?, query_count=?, query_time=?, page_id=?");
						$q->execute(array($pq['id'], $data['count'], $data['time'], $page_id));
					}

				}
			}
		}
	}

}

/**
 * Called when a job has been executed by the job framework.
 * {@link #performance_metrics_page_end()} can still be called for database metrics eftc.
 */
function performance_metrics_job_complete($job = null, $runtime_exception = null) {
	if (!performance_metrics_enabled()) {
		return;
	}
	global $_performance_metrics;
	$job_time = microtime(true) - $_performance_metrics['page_start'];

	// "What jobs take the longest?"
	// "How many jobs are running per hour?"
	// "How many ticker jobs are running per hour?"
	// "What jobs have the most database queries?"
	// "What jobs spend the most time in PHP as opposed to the database?"
	// "Which jobs time out the most?"
	// "How many blockchain requests fail?"
	// "What jobs take the longest requesting URLs?"
	if ($job) {
		$query = "INSERT INTO performance_metrics_jobs SET job_type=:job_type, arg0=:arg0, time_taken=:time_taken, 
				job_failure=:job_failure, runtime_exception=:runtime_exception";
		$args = array(
			'job_type' => $job['job_type'],
			'arg0' => isset($job['arg0']) ? $job['arg0'] : null,
			'time_taken' => $job_time * 1000, /* save in ms */
			'job_failure' => $job['is_error'] ? 1 : 0,
			'runtime_exception' => $runtime_exception ? get_class($runtime_exception) : null,
		);

		list($query, $args) = prepare_timed_data($query, $args);

		$q = db()->prepare($query);
		$q->execute($args);
	}
}

/**
 * Called when the job queue has been executed by the job framework.
 * {@link #performance_metrics_page_end()} can still be called for database metrics etc.
 */
function performance_metrics_queue_complete($user_id, $priority, $job_types, $premium_only) {
	if (!performance_metrics_enabled()) {
		return;
	}
	global $_performance_metrics;
	$queue_time = microtime(true) - $_performance_metrics['page_start'];

	// "How many jobs are being queued at once?"
	// "Which queue types take the longest?"
	$query = "INSERT INTO performance_metrics_queues SET time_taken=:time_taken, user_id=:user_id, priority=:priority, job_types=:job_types, 
		premium_only=:premium_only";
	$args = array(
		'time_taken' => $queue_time * 1000, /* save in ms */
		'user_id' => $user_id ? $user_id : null,
		'priority' => $priority ? $priority : null,
		'job_types' => $job_types ? substr($job_types, 0, 255) : null,
		'premium_only' => $premium_only ? 1 : 0,
	);

	list($query, $args) = prepare_timed_data($query, $args);

	$q = db()->prepare($query);
	$q->execute($args);

}

/**
 * Called when a graph has been rendered by the job framework.
 * {@link #performance_metrics_page_end()} can still be called for database metrics etc.
 */
function performance_metrics_graph_complete($graph) {
	if (!performance_metrics_enabled()) {
		return;
	}
	global $_performance_metrics;
	$graph_time = microtime(true) - $_performance_metrics['page_start'];

	// "What graph types take the longest to render?"
	// "What are the most common graph types?"
	// "How many ticker graphs are being requested?"
	if ($graph) {
		$query = "INSERT INTO performance_metrics_graphs SET graph_type=:graph_type, time_taken=:time_taken, is_logged_in=:is_logged_in, 
			days=:days, has_technicals=:has_technicals";
		$args = array(
			'graph_type' => $graph['graph_type'],
			'time_taken' => $graph_time * 1000, /* save in ms */
			'is_logged_in' => user_logged_in() ? 1 : 0,
			'days' => $graph['days'] ? $graph['days'] : null,
			'has_technicals' => isset($graph['technicals']) && $graph['technicals'] ? 1 : 0,
		);

		list($query, $args) = prepare_timed_data($query, $args);

		$q = db()->prepare($query);
		$q->execute($args);
	}
}

/**
 * Prepare the query, args from timed_sql or timed_curl
 * @return list($query, $args)
 */
function prepare_timed_data($query, $args) {

	if (get_site_config('timed_sql')) {
		// also load DB metrics from timed_sql
		global $global_timed_sql;
		$query .= ", db_prepares=:db_prepares, db_executes=:db_executes, db_fetches=:db_fetches, db_fetch_alls=:db_fetch_alls,
			db_prepare_time=:db_prepare_time, db_execute_time=:db_execute_time, db_fetch_time=:db_fetch_time, db_fetch_all_time=:db_fetch_all_time";
		$args += array(
			'db_prepares' => $global_timed_sql['prepare']['count'],
			'db_executes' => $global_timed_sql['execute']['count'],
			'db_fetches' => $global_timed_sql['fetch']['count'],
			'db_fetch_alls' => $global_timed_sql['fetchAll']['count'],
			'db_prepare_time' => $global_timed_sql['prepare']['time'],
			'db_execute_time' => $global_timed_sql['execute']['time'],
			'db_fetch_time' => $global_timed_sql['fetch']['time'],
			'db_fetch_all_time' => $global_timed_sql['fetchAll']['time'],
		);
	}

	if (get_site_config('timed_curl')) {
		// also load DB metrics from timed_curl
		global $global_timed_curl;
		$query .= ", curl_requests=:curl_requests, curl_request_time=:curl_request_time";
		$args += array(
			'curl_requests' => $global_timed_curl['count'],
			'curl_request_time' => $global_timed_curl['time'],
		);
	}

	return array($query, $args);

}
