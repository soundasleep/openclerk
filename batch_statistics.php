<?php

/**
 * Batch script: calculate site statistics.
 *
 * Arguments (in command line, use "-" for no argument):
 *   $key/1 required the automated key
 */

require("inc/global.php");
require("_batch.php");

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

	is_recent=1
	");
$q->execute($data);
$insert_id = db()->lastInsertId();

crypto_log("Inserted new site statistics ID $insert_id");

batch_footer();
