<?php

/**
 * Batch script: calculate site statistics.
 *
 * Arguments (in command line, use "-" for no argument):
 *   $key/1 required the automated key
 */

require(__DIR__ . "/inc/global.php");
require(__DIR__ . "/_batch.php");

require_batch_key();
batch_header("Batch statistics", "batch_statistics");

crypto_log("Current time: " . date('r'));

// calculate statistics
$data = array();
{
	$q = db()->prepare("SELECT COUNT(*) AS c FROM users");
	$q->execute();
	$c = $q->fetch();
	$data['total_users'] = $c['c'];
}
{
	$q = db()->prepare("SELECT COUNT(*) AS c FROM users WHERE is_disabled=1");
	$q->execute();
	$c = $q->fetch();
	$data['disabled_users'] = $c['c'];
}
{
	$q = db()->prepare("SELECT COUNT(*) AS c FROM users WHERE is_premium=1");
	$q->execute();
	$c = $q->fetch();
	$data['premium_users'] = $c['c'];
}
{
	$q = db()->prepare("SELECT COUNT(*) AS c FROM users WHERE graph_managed_type=?");
	$q->execute(array('none'));
	$c = $q->fetch();
	$data['users_graphs_managed_none'] = $c['c'];
}
{
	$q = db()->prepare("SELECT COUNT(*) AS c FROM users WHERE graph_managed_type=?");
	$q->execute(array('managed'));
	$c = $q->fetch();
	$data['users_graphs_managed_managed'] = $c['c'];
}
{
	$q = db()->prepare("SELECT COUNT(*) AS c FROM users WHERE graph_managed_type=?");
	$q->execute(array('auto'));
	$c = $q->fetch();
	$data['users_graphs_managed_auto'] = $c['c'];
}
{
	$q = db()->prepare("SELECT COUNT(*) AS c FROM users WHERE needs_managed_update=1");
	$q->execute();
	$c = $q->fetch();
	$data['users_graphs_need_update'] = $c['c'];
}
{
	$q = db()->prepare("SELECT COUNT(*) AS c FROM users WHERE subscribe_announcements=1");
	$q->execute();
	$c = $q->fetch();
	$data['users_subscribe_announcements'] = $c['c'];
}
{
	$q = db()->prepare("SELECT SUM(logins_after_disable_warned) AS c FROM users");
	$q->execute();
	$c = $q->fetch();
	$data['user_logins_after_warned'] = $c['c'];
}
{
	$q = db()->prepare("SELECT COUNT(*) AS c FROM users WHERE logins_after_disable_warned <> 0");
	$q->execute();
	$c = $q->fetch();
	$data['users_login_after_warned'] = $c['c'];
}
{
	$q = db()->prepare("SELECT SUM(logins_after_disabled) AS c FROM users");
	$q->execute();
	$c = $q->fetch();
	$data['user_logins_after_disabled'] = $c['c'];
}
{
	$q = db()->prepare("SELECT COUNT(*) AS c FROM users WHERE logins_after_disabled <> 0");
	$q->execute();
	$c = $q->fetch();
	$data['users_login_after_disabled'] = $c['c'];
}

{
	$q = db()->prepare("SELECT jobs.* FROM jobs JOIN users ON jobs.user_id=users.id WHERE users.is_premium=0 AND is_executed=0 ORDER BY jobs.created_at ASC LIMIT 1");
	$q->execute();
	$c = $q->fetch();
	$data["free_delay_minutes"] = $c ? (time() - strtotime($c['created_at'])) / 60 : 0;
}
{
	$q = db()->prepare("SELECT jobs.* FROM jobs JOIN users ON jobs.user_id=users.id WHERE users.is_premium=1 AND is_executed=0 ORDER BY jobs.created_at ASC LIMIT 1");
	$q->execute();
	$c = $q->fetch();
	$data["premium_delay_minutes"] = $c ? (time() - strtotime($c['created_at'])) / 60 : 0;
}
{
	$q = db()->prepare("SELECT COUNT(*) AS c FROM jobs WHERE is_executed=0");
	$q->execute();
	$c = $q->fetch();
	$data['outstanding_jobs'] = $c['c'];
}
{
	$q = db()->prepare("SELECT SUM(job_count) AS jc, SUM(job_errors) AS je FROM external_status WHERE is_recent=1");
	$q->execute();
	$c = $q->fetch();
	$data['external_status_job_count'] = $c['jc'];
	$data['external_status_job_errors'] = $c['je'];
}

{
	$q = db()->prepare("SELECT COUNT(*) AS c FROM pending_subscriptions WHERE is_subscribe=1");
	$q->execute();
	$c = $q->fetch();
	$data['pending_subscriptions'] = $c['c'];
}
{
	$q = db()->prepare("SELECT COUNT(*) AS c FROM pending_subscriptions WHERE is_subscribe=0");
	$q->execute();
	$c = $q->fetch();
	$data['pending_unsubscriptions'] = $c['c'];
}

{
	$q = db()->prepare("SELECT COUNT(*) AS c FROM jobs WHERE is_test_job=1");
	$q->execute();
	$c = $q->fetch();
	$data['jobs_tests'] = $c['c'];
}
{
	$q = db()->prepare("SELECT COUNT(*) AS c FROM jobs WHERE is_timeout=1");
	$q->execute();
	$c = $q->fetch();
	$data['jobs_timeout'] = $c['c'];
}

// calculate MySQL statistics
$q = db()->prepare("SHOW GLOBAL STATUS");
$q->execute();
$mysql_mapping = array(
	'mysql_uptime' => 'Uptime',
	'mysql_threads' => 'Threads_running',
	'mysql_questions' => 'Questions',
	'mysql_slow_queries' => 'Slow_queries',
	'mysql_opens' => 'Opened_tables',
	'mysql_flush_tables' => 'Flush_commands',
	'mysql_open_tables' => 'Open_tables',
);
while ($s = $q->fetch()) {
	if (($pos = array_search($s['Variable_name'], $mysql_mapping)) !== false) {
		$data[$pos] = $s['Value'];
	}
}

$data['disk_free_space'] = disk_free_space('/');

// get system statistics if defined (i.e. not Windows)
$query_extra = "";
if (function_exists('sys_getloadavg')) {
	$top = sys_getloadavg();
	$data['system_load_1min'] = $top[0];
	$data['system_load_5min'] = $top[1];
	$data['system_load_15min'] = $top[2];
	$query_extra = "
		system_load_1min = :system_load_1min,
		system_load_5min = :system_load_5min,
		system_load_15min = :system_load_15min,
	";
}

crypto_log(print_r($data, true));

$q = db()->prepare("UPDATE site_statistics SET is_recent=0 WHERE is_recent=1");
$q->execute();

$q = db()->prepare("INSERT INTO site_statistics SET
	total_users = :total_users,
	disabled_users = :disabled_users,
	premium_users = :premium_users,

	free_delay_minutes = :free_delay_minutes,
	premium_delay_minutes = :premium_delay_minutes,
	outstanding_jobs = :outstanding_jobs,
	external_status_job_count = :external_status_job_count,
	external_status_job_errors = :external_status_job_errors,

	mysql_uptime = :mysql_uptime,
	mysql_threads = :mysql_threads,
	mysql_questions = :mysql_questions,
	mysql_slow_queries = :mysql_slow_queries,
	mysql_opens = :mysql_opens,
	mysql_flush_tables = :mysql_flush_tables,
	mysql_open_tables = :mysql_open_tables,

	users_graphs_managed_none = :users_graphs_managed_none,
	users_graphs_managed_managed = :users_graphs_managed_managed,
	users_graphs_managed_auto = :users_graphs_managed_auto,
	users_graphs_need_update = :users_graphs_need_update,
	users_subscribe_announcements = :users_subscribe_announcements,
	pending_subscriptions = :pending_subscriptions,
	pending_unsubscriptions = :pending_unsubscriptions,

	user_logins_after_warned = :user_logins_after_warned,
	users_login_after_warned = :users_login_after_warned,
	user_logins_after_disabled = :user_logins_after_disabled,
	users_login_after_disabled = :users_login_after_disabled,

	jobs_tests = :jobs_tests,
	jobs_timeout = :jobs_timeout,

	disk_free_space = :disk_free_space,

	$query_extra

	is_recent=1
	");
$q->execute($data);
$insert_id = db()->lastInsertId();

crypto_log("Inserted new site statistics ID $insert_id");

batch_footer();
