<?php

/**
 * Batch script: look to see if we need to queue in any jobs, and then insert them in.
 *
 * Arguments:
 *   $key required the automated key
 *   $user optional restrict job generation to only this user ID (e.g. the system user)
 *   $priority optional priority for generated jobs (defaults to 10, lower = higher priority)
 *   $job_type optional restrict job generation to only this type of job, comma-separated list
 *
 * For example, there should both be a generic job queue script call, and another script call
 * just to check for updated payment balances:
 *   batch_queue?key=...&user=100&priority=-20&job_type=blockchain,outstanding
 */

require("inc/global.php");

if (!(isset($argv) && $argv[1] == get_site_config("automated_key")) && require_get("key") != get_site_config("automated_key"))
	throw new Exception("Invalid key");

// TODO all of these need to be duplicated for e.g. premium users
$user_id = false;
if (isset($argv[2])) {
	$user_id = $argv[2];
} else if (require_get("user", false)) {
	$user_id = require_get("user");
}

$priority = 10;	// default priority
if (isset($argv[2])) {
	$priority = $argv[2];
} else if (require_get("priority", false)) {
	$priority = require_get("priority");
}

$job_type = null;
if (isset($argv[2])) {
	$job_type = explode(",", $argv[2]);
} else if (require_get("job_type", false)) {
	$job_type = explode(",", require_get("job_type"));
}

function added_job($job) {
	echo "\n<li>Added job " . htmlspecialchars(print_r($job, true)) . " (<a href=\"" . htmlspecialchars(url_for('batch_run',
			array('key' => require_get("key", false), 'job_id' => $job['id']))) . "\">run job now</a>)</li>";
}

// standard jobs involve an 'id' from a table and a 'user_id' from the same table
$standard_jobs = array(
	array('table' => 'exchanges', 'type' => 'ticker', 'user_id' => get_site_config('system_user_id')),
	array('table' => 'addresses', 'type' => 'blockchain'),
	array('table' => 'accounts_generic', 'type' => 'generic'),
	array('table' => 'accounts_btce', 'type' => 'btce'),
	array('table' => 'accounts_mtgox', 'type' => 'mtgox'),
	array('table' => 'accounts_poolx', 'type' => 'poolx'),
	array('table' => 'summaries', 'type' => 'summary'),
	array('table' => 'outstanding_premiums', 'type' => 'outstanding', 'query' => ' AND is_paid=0', 'user_id' => get_site_config('system_user_id')),
);

foreach ($standard_jobs as $standard) {
	if ($job_type && !in_array($standard['type'], $job_type)) {
		echo "\n<li>Skipping " . htmlspecialchars($standard['type']) . ": not in job types [" . htmlspecialchars(implode(", ", $job_type)) . "].</li>";
		continue;
	}

	$query_extra = isset($standard['query']) ? $standard['query'] : "";
	$args_extra = isset($standard['args']) ? $standard['args'] : array();
	if ($user_id && !isset($standard['user_id'])) {
		$query_extra .= " AND user_id=?";
		$args_extra[] = $user_id;
	}

	$q = db()->prepare("SELECT * FROM " . $standard['table'] . " WHERE (last_queue <= DATE_SUB(NOW(), INTERVAL ? HOUR) OR ISNULL(last_queue)) $query_extra");
	$q->execute(array_join(array(get_site_config('refresh_queue_hours')), $args_extra));
	while ($address = $q->fetch()) {
		$job = array(
			"priority" => $priority,
			"type" => $standard['type'],
			"user_id" => isset($standard['user_id']) ? $standard['user_id'] : $address['user_id'],
			"arg_id" => $address['id'],
		);

		// make sure the new job doesn't already exist
		$q2 = db()->prepare("SELECT * FROM jobs WHERE job_type=:type AND arg_id=:arg_id AND priority=:priority AND is_executed=0 LIMIT 1");
		$q2->execute(array(
			'type' => $job['type'],
			'arg_id' => $job['arg_id'],
			'priority' => $priority, // so we can override priorities as necessary
		));
		if (!$q2->fetch()) {
			$q2 = db()->prepare("INSERT INTO jobs SET priority=:priority, job_type=:type, user_id=:user_id, arg_id=:arg_id");
			$q2->execute($job);
			$job['id'] = db()->lastInsertId();
			added_job($job);
		}

		// update the address
		$q2 = db()->prepare("UPDATE addresses SET last_queue=NOW() WHERE id=?");
		$q2->execute(array($address['id']));
	}
}

echo "\n<li>Complete.";
