<?php

/**
 * Batch script: find a job to execute, and then execute it.
 * We don't use meta-jobs to insert in new jobs, because we implement
 * job priority and we don't want to have to also use 'execute_after'.
 *
 * Arguments (in command line, use "-" for no argument):
 *   $key/1 required the automated key
 *   $job_type/2 optional restrict job execution to only this type of job, comma-separated list
 */

if (!defined('ADMIN_RUN_JOB')) {
	require("inc/global.php");
}
require("_batch.php");

require_batch_key();
batch_header("Batch run", "batch_run");

$job_types = array();	// default to running all jobs
if (isset($argv[2]) && $argv[2] && $argv[2] != "-") {
	$job_types = explode(",", $argv[2]);
} else if (require_get("job_type", false)) {
	$job_types = explode(",", require_get("job_type"));
}

if (require_get("job_id", false)) {
	// run a particular job, even if it's already been executed
	$q = db()->prepare("SELECT * FROM jobs WHERE id=?");
	$q->execute(array(require_get("job_id")));
	$job = $q->fetch();
} else {
	// select a particular type of job (allows more fine-grained control over regularly important jobs, such as ticker)
	$job_type_where = "";
	if ($job_types) {
		foreach ($job_types as $jt) {
			$job_type_where .= ($job_type_where ? ", " : "") . "'" . $jt . "'";
		}
		$job_type_where = " AND job_type IN (" . $job_type_where . ") ";
	}

	// select the most important job to execute next
	$q = db()->prepare("SELECT * FROM jobs WHERE is_executed=0 AND is_executing=0 $job_type_where ORDER BY priority ASC, id ASC LIMIT 20");
	$q->execute();

	// iterate until we find a job that we can actually run right now
	while ($job = $q->fetch()) {
		$throttle = get_site_config('throttle_' . $job['job_type'], false);
		if ($throttle) {
			// find the last executed job
			$q1 = db()->prepare("SELECT * FROM jobs WHERE is_executed=1 AND job_type=? AND executed_at > date_sub(now(), interval ? second) LIMIT 1");
			$q1->execute(array($job['job_type'], $throttle));
			if ($early = $q1->fetch()) {
				crypto_log("Cannot run job " . $job['id'] . " (" . $job['job_type'] . ": another job " . $early['job_type'] . " was run less than $throttle seconds ago (" . $early['id'] . ")");
			} else {
				// we've found a job we can execute
				break;
			}
		} else {
			// we've found a job we can execute
			break;
		}
	}
}

if (!$job) {
	// nothing to do!
	crypto_log("No job to execute.");
	return;
}

crypto_log("Current time: " . date('r'));

// otherwise, we'll want to actually execute something, based on the job type
// TODO remove the navigation links once we have an actual job admin interface
crypto_log("Executing job " . htmlspecialchars(print_r($job, true)) . " (<a href=\"" . htmlspecialchars(url_for('batch_run',
	array('key' => require_get("key", false), 'job_id' => $job['id']))) . "\">re-run job</a>) (<a href=\"" . htmlspecialchars(url_for('batch_run',
	array('key' => require_get("key", false)))) . "\">next job</a>)");

$runtime_exception = null;
try {
	// have we executed this job too many times already?
	if ($job['execution_count'] >= get_site_config("max_job_executions")) {
		// TODO this job should be debugged in dev and fixed so that an execption can be thrown instead
		crypto_log("Job has been executed too many times (" . number_format($job['execution_count']) . "): marking as failed");
		throw new RuntimeAPIException("An uncaught error occured multiple times");
	} else {
		// update the job execution count
		$q = db()->prepare("UPDATE jobs SET is_executing=1,execution_count=execution_count+1 WHERE id=?");
		$q->execute(array($job['id']));
	}

	switch ($job['job_type']) {
		// ticker jobs
		case "ticker":
			require("jobs/ticker.php");
			break;

		// address jobs
		case "blockchain":
			require("jobs/blockchain.php");
			break;

		case "litecoin":
			require("jobs/litecoin.php");
			break;

		case "feathercoin":
			require("jobs/feathercoin.php");
			break;

		case "litecoin_block":
			require("jobs/litecoin_block.php");
			break;

		case "feathercoin_block":
			require("jobs/feathercoin_block.php");
			break;

		case "generic":
			require("jobs/generic.php");
			break;

		case "btce":
			require("jobs/btce.php");
			break;

		case "mtgox":
			require("jobs/mtgox.php");
			break;

		case "vircurex":
			require("jobs/vircurex.php");
			break;

		case "poolx":
			require("jobs/poolx.php");
			break;

		case "wemineltc":
			require("jobs/wemineltc.php");
			break;

		case "givemeltc":
			require("jobs/givemeltc.php");
			break;

		case "slush":
			require("jobs/slush.php");
			break;

		case "litecoinglobal":
			require("jobs/litecoinglobal.php");
			break;

		case "securities_litecoinglobal":
			require("jobs/securities_litecoinglobal.php");
			break;

		case "btct":
			require("jobs/btct.php");
			break;

		case "securities_btct":
			require("jobs/securities_btct.php");
			break;

		case "cryptostocks":
			require("jobs/cryptostocks.php");
			break;

		case "securities_cryptostocks":
			require("jobs/securities_cryptostocks.php");
			break;

		case "bips":
			require("jobs/bips.php");
			break;

		case "btcguild":
			require("jobs/btcguild.php");
			break;

		case "50btc":
			require("jobs/50btc.php");
			break;

		case "hypernova":
			require("jobs/hypernova.php");
			break;

		case "ltcmineru":
			require("jobs/ltcmineru.php");
			break;

		// summary jobs
		case "summary":
			require("jobs/summary.php");
			break;

		// cleanup jobs, admin jobs etc
		case "outstanding":
			require("jobs/outstanding.php");
			break;

		case "expiring":
			require("jobs/expiring.php");
			break;

		case "expire":
			require("jobs/expire.php");
			break;

		case "cleanup":
			require("jobs/cleanup.php");
			break;

		default:
			throw new JobException("Unknown job type '" . htmlspecialchars($job['job_type']) . "'");

	}
} catch (Exception $e) {
	// if an exception occurs, we still want to remove the job from the queue, even though we
	// may not have inserted in any valid data
	$runtime_exception = $e;
}

// delete job
$q = db()->prepare("UPDATE jobs SET is_executed=1,is_executing=0,is_error=?,executed_at=NOW() WHERE id=? LIMIT 1");
$q->execute(array(($runtime_exception === null ? 0 : 1), $job['id']));

// rethrow exception if necessary
if ($runtime_exception !== null) {
	throw new WrappedJobException($runtime_exception, $job['id']);
}

echo "\n<li>Job successful.";

batch_footer();

function insert_new_balance($job, $account, $exchange, $currency, $balance) {

	crypto_log("$exchange $currency balance for user " . $job['user_id'] . ": " . $balance);

	// disable old instances
	$q = db()->prepare("UPDATE balances SET is_recent=0 WHERE is_recent=1 AND user_id=:user_id AND exchange=:exchange AND account_id=:account_id AND currency=:currency");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
	));

	// all other data from today is now old
	// NOTE if the system time changes between the next two commands, then we may erraneously
	// specify that there is no valid daily data. one solution is to specify NOW() as $created_at rather than
	// relying on MySQL
	$q = db()->prepare("UPDATE balances SET is_daily_data=0 WHERE is_daily_data=1 AND user_id=:user_id AND account_id=:account_id AND exchange=:exchange AND currency=:currency AND
		date_format(created_at, '%d-%m-%Y') = date_format(now(), '%d-%m-%Y')");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
	));

	// we have a balance; update the database
	$q = db()->prepare("INSERT INTO balances SET user_id=:user_id, exchange=:exchange, account_id=:account_id, balance=:balance, currency=:currency, job_id=:job_id, is_recent=1, is_daily_data=1");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
		"balance" => $balance,
		"job_id" => $job['id'],
		// we ignore server_time
	));
	crypto_log("Inserted new $exchange $currency balances id=" . db()->lastInsertId());

}