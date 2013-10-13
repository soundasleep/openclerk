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

require(__DIR__ . "/inc/global.php");
require(__DIR__ . "/_batch.php");

require_batch_key();
batch_header("Batch queue", "batch_queue");

if (!get_site_config('jobs_enabled')) {
	// we've disabled jobs for now
	crypto_log("Job execution disabled ('jobs_enabled').");
	die;
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

// standard jobs involve an 'id' from a table and a 'user_id' from the same table (unless 'user_id' is set)
// the table needs 'last_queue' unless 'always' is specified (in which case, it will always happen)
// if no 'user_id' is specified, then the user will also be checked for disable status
$standard_jobs = array(
	array('table' => 'exchanges', 'type' => 'ticker', 'user_id' => get_site_config('system_user_id')),
	array('table' => 'addresses', 'type' => 'blockchain', 'query' => ' AND currency=\'btc\''),
	array('table' => 'addresses', 'type' => 'litecoin', 'query' => ' AND currency=\'ltc\''), // make sure to add litecoin_block job below too
	array('table' => 'addresses', 'type' => 'feathercoin', 'query' => ' AND currency=\'ftc\''), // make sure to add feathercoin_block job below too
	array('table' => 'addresses', 'type' => 'ppcoin', 'query' => ' AND currency=\'ppc\''), // make sure to add feathercoin_block job below too
	array('table' => 'addresses', 'type' => 'novacoin', 'query' => ' AND currency=\'nvc\''), // make sure to add feathercoin_block job below too
	array('table' => 'accounts_generic', 'type' => 'generic'),
	array('table' => 'accounts_btce', 'type' => 'btce'),
	array('table' => 'accounts_mtgox', 'type' => 'mtgox'),
	array('table' => 'accounts_vircurex', 'type' => 'vircurex'),
	array('table' => 'accounts_poolx', 'type' => 'poolx'),
	array('table' => 'accounts_wemineltc', 'type' => 'wemineltc'),
	array('table' => 'accounts_givemecoins', 'type' => 'givemecoins'),
	array('table' => 'accounts_slush', 'type' => 'slush'),
	array('table' => 'accounts_litecoinglobal', 'type' => 'litecoinglobal'),
	array('table' => 'securities_litecoinglobal', 'type' => 'securities_litecoinglobal', 'user_id' => get_site_config('system_user_id')),
	array('table' => 'accounts_btct', 'type' => 'btct'),
	array('table' => 'securities_btct', 'type' => 'securities_btct', 'user_id' => get_site_config('system_user_id')),
	array('table' => 'accounts_cryptostocks', 'type' => 'cryptostocks'),
	array('table' => 'securities_cryptostocks', 'type' => 'securities_cryptostocks', 'user_id' => get_site_config('system_user_id')),
	array('table' => 'accounts_bips', 'type' => 'bips'),
	array('table' => 'accounts_btcguild', 'type' => 'btcguild'),
	array('table' => 'accounts_50btc', 'type' => '50btc'),
	array('table' => 'accounts_hypernova', 'type' => 'hypernova'),
	array('table' => 'accounts_ltcmineru', 'type' => 'ltcmineru'),
	array('table' => 'accounts_miningforeman', 'type' => 'miningforeman'),
	array('table' => 'accounts_miningforeman_ftc', 'type' => 'miningforeman_ftc'),
	array('table' => 'accounts_havelock', 'type' => 'havelock'),
	array('table' => 'securities_havelock', 'type' => 'securities_havelock', 'user_id' => get_site_config('system_user_id')),
	array('table' => 'accounts_bitminter', 'type' => 'bitminter'),
	array('table' => 'accounts_liteguardian', 'type' => 'liteguardian'),
	array('table' => 'users', 'type' => 'sum', 'user_id_field' => 'id'), /* so that total BTC/LTC etc will be updated together before conversion summaries */
	array('table' => 'summaries', 'type' => 'summary'),
	array('table' => 'outstanding_premiums', 'type' => 'outstanding', 'query' => ' AND is_paid=0 AND is_unpaid=0', 'user_id' => get_site_config('system_user_id')),
	array('table' => 'users', 'type' => 'expiring', 'query' => ' AND is_premium=1
		AND is_reminder_sent=0
		AND NOT ISNULL(email) AND LENGTH(email) > 0
		AND NOW() > DATE_SUB(premium_expires, INTERVAL ' . get_site_config('premium_reminder_days') . ' DAY)', 'user_id' => get_site_config('system_user_id'), 'always' => true),
	array('table' => 'users', 'type' => 'expire', 'query' => ' AND is_premium=1
		AND NOW() > premium_expires', 'user_id' => get_site_config('system_user_id'), 'always' => true),
	array('table' => 'users', 'type' => 'disable_warning', 'query' => ' AND is_premium=0 AND is_disabled=0
		AND is_disable_warned=0 AND is_system=0
		AND DATE_ADD(GREATEST(IFNULL(last_login, 0),
				IFNULL(DATE_ADD(premium_expires, INTERVAL ' . get_site_config('user_expiry_days') . ' DAY), 0),
				created_at), INTERVAL ' . (get_site_config('user_expiry_days') * 0.8) . ' DAY) < NOW()', 'user_id' => get_site_config('system_user_id'), 'always' => true),
	array('table' => 'users', 'type' => 'disable', 'query' => ' AND is_premium=0 AND is_disabled=0
		AND is_disable_warned=1 AND is_system=0
		AND DATE_ADD(GREATEST(IFNULL(last_login, 0),
				IFNULL(DATE_ADD(premium_expires, INTERVAL ' . get_site_config('user_expiry_days') . ' DAY), 0),
				created_at), INTERVAL ' . (get_site_config('user_expiry_days')) . '+1 DAY) < NOW()', 'user_id' => get_site_config('system_user_id'), 'always' => true),
	array('table' => 'securities_update', 'type' => 'securities_update', 'user_id' => get_site_config('system_user_id')),
);

crypto_log("Current time: " . date('r'));

// get all disabled users
$disabled = array();
$q = db()->prepare("SELECT * FROM users WHERE is_disabled=1");
$q->execute();
while ($d = $q->fetch()) {
	$disabled[$d['id']] = $d;
}

foreach ($standard_jobs as $standard) {
	if ($job_type && !in_array($standard['type'], $job_type)) {
		crypto_log("Skipping " . htmlspecialchars($standard['type']) . ": not in job types [" . htmlspecialchars(implode(", ", $job_type)) . "].");
		continue;
	}
	if ($premium_only && isset($standard['user_id'])) {
		crypto_log("Skipping " . htmlspecialchars($standard['type']) . ": not premium user type job.");
		continue;
	}

	$always = isset($standard['always']) && $standard['always'];
	$field = isset($standard['user_id_field']) ? $standard['user_id_field'] : 'user_id';

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
				$new_user_premium = get_site_config('new_user_premium_update_hours') ?
					"OR created_at > DATE_SUB(NOW(), interval " . get_site_config('new_user_premium_update_hours') . " hour)" : "";
				$query_extra .= " AND $field IN (SELECT id AS $field FROM users WHERE is_premium=1 $new_user_premium)";
			}
		} else {
			// we want to run system jobs at least every 0.1 hours = 6 minutes
			$args[] = isset($standard['hours']) ? $standard['hours'] : (($user_id == get_site_config('system_user_id') || (isset($standard['user_id']) && $standard['user_id'] == get_site_config('system_user_id'))) ? get_site_config('refresh_queue_hours_system') : get_site_config('refresh_queue_hours'));
		}
	}

	$queue_field = isset($standard['queue_field']) ? $standard['queue_field'] : 'last_queue';

	// multiply queue_hours by 0.8 to ensure that user jobs are always executed within the specified timeframe
	$q = db()->prepare("SELECT * FROM " . $standard['table'] . " WHERE " . ($always ? "1" : "($queue_field <= DATE_SUB(NOW(), INTERVAL (? * 0.8) HOUR) OR ISNULL($queue_field))") . " $query_extra");
	$q->execute(array_join($args, $args_extra));
	$disabled_count = 0;
	while ($address = $q->fetch()) {
		$job = array(
			"priority" => $priority,
			"type" => $standard['type'],
			"user_id" => isset($standard['user_id']) ? $standard['user_id'] : $address[$field],	/* $field so we can select users.id as user_id */
			"arg_id" => $address['id'],
		);

		// check that this user is not disabled
		if (isset($disabled[$job['user_id']])) {
			if ($disabled_count == 0) {
				crypto_log("Skipping job '" . $standard['type'] . "' for user " . $job['user_id'] . ": user is disabled");
			}
			$disabled_count++;
			continue;
		}

		insert_new_job($job, $address, $queue_field);

		// update the address
		try {
			// only update last_queue if that field actually exists
			if (isset($address[$queue_field]) || array_key_exists($queue_field, $address) /* necessary to set last_queue when last_queue is null: isset() returns false on null */) {
				$q2 = db()->prepare("UPDATE " . $standard['table'] . " SET $queue_field=NOW() WHERE id=?");
				$q2->execute(array($address['id']));
			}
		} catch (PDOException $e) {
			throw new Exception("Could not queue jobs for table " . $standard['table'] . ": " . $e->getMessage(), (int) $e->getCode(), $e);
		}
	}

	if ($disabled_count > 1) {
		crypto_log("Also skipped another " . number_format($disabled_count) . " " . $standard['type'] . " jobs due to disabled users");
	}
}

if (!$premium_only) {
	$block_jobs = array('litecoin_block', 'feathercoin_block', 'ppcoin_block', 'novacoin_block');
	foreach ($block_jobs as $name) {
		// as often as we can (or on request), run litecoin_block jobs
		if (!$job_type || in_array($name, $job_type)) {
			insert_new_job(array(
				'priority' => $priority,
				'type' => $name,
				'user_id' => get_site_config('system_user_id'),
				'arg_id' => -1,
			), false);
		}
	}

	// reset jobs that have crashed
	// if a job is currently running, this won't have any effect, unless it crashes right now
	$q = db()->prepare("UPDATE jobs SET is_executing=0 WHERE is_executing=1");
	$q->execute();
	crypto_log("Reset old executing jobs");

	// once a day (at 6am) (or on request), run cleanup jobs
	/*
	if (date('H') == 6 || in_array("cleanup", $job_type)) {
		insert_new_job(array(
			'priority' => $priority,
			'type' => 'cleanup',
			'user_id' => get_site_config('system_user_id'),
			'arg_id' => -1,
		), false);
	}
	*/

}

/**
 * @param $old the previous database row that was used to generae this job (may have last_queue), or {@code false}
 * 		if this job has no parent database row (e.g. litecoin_block jobs)
 */
function insert_new_job($job, $old, $queue_field = 'last_queue') {
	// make sure the new job doesn't already exist
	$q2 = db()->prepare("SELECT * FROM jobs WHERE job_type=:type AND arg_id=:arg_id AND priority <= :priority AND is_executed=0 LIMIT 1");
	$q2->execute(array(
		'type' => $job['type'],
		'arg_id' => $job['arg_id'],
		'priority' => $job['priority'], // so we can override priorities as necessary
	));
	$existing = $q2->fetch();
	if (!$existing) {
		$q2 = db()->prepare("INSERT INTO jobs SET priority=:priority, job_type=:type, user_id=:user_id, arg_id=:arg_id");
		$q2->execute($job);
		$job['id'] = db()->lastInsertId();
		added_job($job, ($old && isset($old[$queue_field])) ? " - last queue " . recent_format_html($old[$queue_field]) : " - no last queue" );
	} else {
		crypto_log("Job " . htmlspecialchars(print_r($job, true)) . " already exists (<a href=\"" . htmlspecialchars(url_for('batch_run',
			array('key' => require_get("key", false), 'job_id' => $existing['id']))) . "\">run job now</a>)");
	}

}

function added_job($job, $suffix) {
	echo crypto_log("Added job " . htmlspecialchars(print_r($job, true)) . " $suffix (<a href=\"" . htmlspecialchars(url_for('batch_run',
			array('key' => require_get("key", false), 'job_id' => $job['id']))) . "\">run job now</a>)");
}

crypto_log("Complete.");

batch_footer();
