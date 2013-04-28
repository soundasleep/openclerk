<?php

/**
 * Batch script: look to see if we need to queue in any jobs, and then insert them in.
 *
 * Arguments (in command line, use "-" for no argument):
 *   $key/1 required the automated key
 *   $user/2 optional restrict job generation to only this user ID (e.g. the system user)
 *   $priority/3 optional priority for generated jobs (defaults to 10, lower = higher priority)
 *   $job_type/4 optional restrict job generation to only this type of job, comma-separated list
 *   $premium_only/5 optional only execute premium jobs
 *
 * For example, when deploying, you should have the following job queue script calls:
 * (normal jobs, 6 hours)
 *   batch_queue?key=...&priority=10
 * (premium user jobs, 30 mins)
 *   batch_queue?key=...&priority=5&premium_only=1
 * (admin jobs, 5 mins) [5 mins for LTC transactions]
 *   batch_queue?key=...&user=100&priority=-20&job_type=blockchain,litecoin,outstanding,expiring,expire
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
	page_header("Queue", "page_batch_queue", $options);
}

// TODO all of these need to be duplicated for e.g. premium users
$user_id = false;
if (isset($argv[2]) && $argv[2] && $argv[2] != "-") {
	$user_id = $argv[2];
} else if (require_get("user", false)) {
	$user_id = require_get("user");
}

$priority = 10;	// default priority
if (isset($argv[3]) && $argv[3] && $argv[3] != "-") {
	$priority = $argv[3];
} else if (require_get("priority", false)) {
	$priority = require_get("priority");
}

$job_type = array();
if (isset($argv[4]) && $argv[4] && $argv[4] != "-") {
	$job_type = explode(",", $argv[4]);
} else if (require_get("job_type", false)) {
	$job_type = explode(",", require_get("job_type"));
}

$premium_only = false;
if (isset($argv[5]) && $argv[5] && $argv[5] != "-") {
	$premium_only = explode(",", $argv[5]);
} else if (require_get("premium_only", false)) {
	$premium_only = explode(",", require_get("premium_only"));
}

function added_job($job) {
	echo "\n<li>Added job " . htmlspecialchars(print_r($job, true)) . " (<a href=\"" . htmlspecialchars(url_for('batch_run',
			array('key' => require_get("key", false), 'job_id' => $job['id']))) . "\">run job now</a>)</li>";
}

// standard jobs involve an 'id' from a table and a 'user_id' from the same table (unless 'user_id' is set)
// the table needs 'last_queue' unless 'always' is specified (in which case, it will always happen)
$standard_jobs = array(
	array('table' => 'exchanges', 'type' => 'ticker', 'user_id' => get_site_config('system_user_id')),
	array('table' => 'addresses', 'type' => 'blockchain', 'query' => ' AND currency=\'btc\''),
	array('table' => 'addresses', 'type' => 'litecoin', 'query' => ' AND currency=\'ltc\''),
	array('table' => 'accounts_generic', 'type' => 'generic'),
	array('table' => 'accounts_btce', 'type' => 'btce'),
	array('table' => 'accounts_mtgox', 'type' => 'mtgox'),
	array('table' => 'accounts_vircurex', 'type' => 'vircurex'),
	array('table' => 'accounts_poolx', 'type' => 'poolx'),
	array('table' => 'accounts_litecoinglobal', 'type' => 'litecoinglobal'),
	array('table' => 'securities_litecoinglobal', 'type' => 'securities_litecoinglobal', 'user_id' => get_site_config('system_user_id')),
	array('table' => 'accounts_btct', 'type' => 'btct'),
	array('table' => 'securities_btct', 'type' => 'securities_btct', 'user_id' => get_site_config('system_user_id')),
	array('table' => 'summaries', 'type' => 'summary'),
	array('table' => 'outstanding_premiums', 'type' => 'outstanding', 'query' => ' AND is_paid=0 AND is_unpaid=0', 'user_id' => get_site_config('system_user_id')),
	array('table' => 'users', 'type' => 'expiring', 'query' => ' AND is_premium=1
		AND is_reminder_sent=0
		AND NOT ISNULL(email) AND LENGTH(email) > 0
		AND NOW() > DATE_SUB(premium_expires, INTERVAL ' . get_site_config('premium_reminder_days') . ' DAY)', 'user_id' => get_site_config('system_user_id'), 'always' => true),
	array('table' => 'users', 'type' => 'expire', 'query' => ' AND is_premium=1
		AND NOW() > premium_expires', 'user_id' => get_site_config('system_user_id'), 'always' => true),
);

foreach ($standard_jobs as $standard) {
	if ($job_type && !in_array($standard['type'], $job_type)) {
		echo "\n<li>Skipping " . htmlspecialchars($standard['type']) . ": not in job types [" . htmlspecialchars(implode(", ", $job_type)) . "].</li>";
		continue;
	}
	if ($premium_only && isset($standard['user_id'])) {
		echo "\n<li>Skipping " . htmlspecialchars($standard['type']) . ": not premium user type job.</li>";
		continue;
	}

	$always = isset($standard['always']) && $standard['always'];

	$query_extra = isset($standard['query']) ? $standard['query'] : "";
	$args_extra = isset($standard['args']) ? $standard['args'] : array();
	if ($user_id && !isset($standard['user_id'])) {
		$query_extra .= " AND user_id=?";
		$args_extra[] = $user_id;
	}

	$args = array();

	if (!$always) {
		if ($premium_only) {
			$args[] = get_site_config('refresh_queue_hours_premium');
			if (!isset($standard['user_id'])) {
				$query_extra .= " AND user_id IN (SELECT id AS user_id FROM users WHERE is_premium=1)";
			}
		} else {
			// we want to run system jobs at least every 0.1 hours = 6 minutes
			$args[] = ($user_id == get_site_config('system_user_id')) ? get_site_config('refresh_queue_hours_system') : get_site_config('refresh_queue_hours');
		}
	}

	$q = db()->prepare("SELECT * FROM " . $standard['table'] . " WHERE " . ($always ? "1" : "(last_queue <= DATE_SUB(NOW(), INTERVAL ? HOUR) OR ISNULL(last_queue))") . " $query_extra");
	$q->execute(array_join($args, $args_extra));
	while ($address = $q->fetch()) {
		$job = array(
			"priority" => $priority,
			"type" => $standard['type'],
			"user_id" => isset($standard['user_id']) ? $standard['user_id'] : $address['user_id'],
			"arg_id" => $address['id'],
		);

		insert_new_job($job);

		// update the address
		$q2 = db()->prepare("UPDATE addresses SET last_queue=NOW() WHERE id=?");
		$q2->execute(array($address['id']));
	}
}

if (!$premium_only) {
	// as often as we can (or on request), run litecoin_block jobs
	if (!$job_type || in_array("litecoin_block", $job_type)) {
		insert_new_job(array(
			'priority' => $priority,
			'type' => 'litecoin_block',
			'user_id' => get_site_config('system_user_id'),
			'arg_id' => -1,
		));
	}

	// once a day (at 6am) (or on request), run cleanup jobs
	if (date('H') == 6 || in_array("cleanup", $job_type)) {
		insert_new_job(array(
			'priority' => $priority,
			'type' => 'cleanup',
			'user_id' => get_site_config('system_user_id'),
			'arg_id' => -1,
		));
	}

}

function insert_new_job($job) {
	// make sure the new job doesn't already exist
	$q2 = db()->prepare("SELECT * FROM jobs WHERE job_type=:type AND arg_id=:arg_id AND priority=:priority AND is_executed=0 LIMIT 1");
	$q2->execute(array(
		'type' => $job['type'],
		'arg_id' => $job['arg_id'],
		'priority' => $job['priority'], // so we can override priorities as necessary
	));
	if (!$q2->fetch()) {
		$q2 = db()->prepare("INSERT INTO jobs SET priority=:priority, job_type=:type, user_id=:user_id, arg_id=:arg_id");
		$q2->execute($job);
		$job['id'] = db()->lastInsertId();
		added_job($job);
	}

}

echo "\n<li>Complete.";

if (require_get("key", false)) {
	// we're running from a web browser
	// include page gen times etc
	page_footer();
}
