<?php

/**
 * Batch script: find a job to execute, and then execute it.
 * We don't use meta-jobs to insert in new jobs, because we implement
 * job priority and we don't want to have to also use 'execute_after'.
 *
 * Arguments (in command line, use "-" for no argument):
 *   $key/1 required the automated key
 *   $job_type/2 optional restrict job execution to only this type of job, comma-separated list
 *   $job_id/3 optional run the given job ID
 *   $force/4 optional if true, force the job to run even if it has failed before
 */

define('BATCH_JOB_START', microtime(true));

if (!defined('ADMIN_RUN_JOB')) {
	require(__DIR__ . "/inc/global.php");
}
require(__DIR__ . "/_batch.php");

require_batch_key();
batch_header("Batch run", "batch_run");

$job_types = array();	// default to running all jobs
if (isset($argv[2]) && $argv[2] && $argv[2] != "-") {
	$job_types = explode(",", $argv[2]);
} else if (require_get("job_type", false)) {
	$job_types = explode(",", require_get("job_type"));
}

if (isset($argv[3]) && $argv[3] && $argv[3] != "-") {
        // run a particular job, even if it's already been executed
        $q = db()->prepare("SELECT * FROM jobs WHERE id=?");
        $q->execute(array((int) $argv[3]));
        $job = $q->fetch();
} else if (require_get("job_id", false)) {
	// run a particular job, even if it's already been executed
	$q = db()->prepare("SELECT * FROM jobs WHERE id=?");
	$q->execute(array((int) require_get("job_id")));
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

	// find all jobs that have crashed (that have taken longer than five minutes) and mark them as errored
	$q = db()->prepare("UPDATE jobs SET is_executing=0,execution_count=execution_count+1,is_error=1,is_timeout=1 WHERE is_executing=1 AND
		((is_test_job=0 AND execution_started < DATE_SUB(NOW(), INTERVAL 5 MINUTE)) OR
		(is_test_job=1 AND execution_started < DATE_SUB(NOW(), INTERVAL 1 MINUTE)))");
	$q->execute();

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

$force = require_get('force', false) || (isset($argv[4]) && $argv[4] != "-" && $argv[4]);

if (!$job) {
	// nothing to do!
	crypto_log("No job to execute.");
	return;
} else if (!get_site_config('jobs_enabled')) {
	// we've disabled jobs for now
	crypto_log("Job execution disabled ('jobs_enabled').");
	return;
}

crypto_log("Current time: " . date('r'));

// otherwise, we'll want to actually execute something, based on the job type
// TODO remove the navigation links once we have an actual job admin interface
crypto_log("Executing job " . htmlspecialchars(print_r($job, true)) . " (<a href=\"" . htmlspecialchars(url_for('batch_run',
	array('key' => require_get("key", false), 'job_id' => $job['id'], 'force' => 1))) . "\">re-run job</a>) (<a href=\"" . htmlspecialchars(url_for('batch_run',
	array('key' => require_get("key", false)))) . "\">next job</a>)");

$runtime_exception = null;
try {
	// have we executed this job too many times already?
	// (we check this here so our exception handling code below can capture it)
	if ($job['is_test_job'] && $job['is_error'] && !$force) {
		crypto_log("Job is a test job and threw an error straight away; marking as failed");
		if ($job['is_timeout']) {
			throw new ExternalAPIException("Local timeout");
		} else {
			throw new ExternalAPIException("Job failed for an unknown reason");
		}
	} else if ($job['execution_count'] >= get_site_config("max_job_executions") && !$force) {
		// TODO this job should be debugged in dev and fixed so that an execption can be thrown instead
		crypto_log("Job has been executed too many times (" . number_format($job['execution_count']) . "): marking as failed");
		throw new ExternalAPIException("An uncaught error occured multiple times");
	} else {
		// update old jobs that they are no longer recent
		// assumes all jobs can be grouped by (job_type,user_id,arg_id)
		$q = db()->prepare("UPDATE jobs SET is_recent=0 WHERE is_recent=1 AND job_type=? AND user_id=? AND arg_id=?");
		$q->execute(array($job['job_type'], $job['user_id'], $job['arg_id']));

		// update the job execution count
		$q = db()->prepare("UPDATE jobs SET is_executing=1,execution_count=execution_count+1,is_recent=1,execution_started=NOW() WHERE id=?");
		$q->execute(array($job['id']));
	}

	// sanity check
	if (!function_exists('curl_init')) {
		throw new Exception("curl_init() function does not exist");
	}

	switch ($job['job_type']) {
		// ticker jobs
		case "ticker":
			require(__DIR__ . "/jobs/ticker.php");
			break;

		// address jobs
		case "blockchain":
			require(__DIR__ . "/jobs/blockchain.php");
			break;

		case "litecoin":
			require(__DIR__ . "/jobs/litecoin.php");
			break;

		case "feathercoin":
			require(__DIR__ . "/jobs/feathercoin.php");
			break;

		case "ppcoin":
			require(__DIR__ . "/jobs/ppcoin.php");
			break;

		case "novacoin":
			require(__DIR__ . "/jobs/novacoin.php");
			break;

		case "primecoin":
			require(__DIR__ . "/jobs/primecoin.php");
			break;

		case "terracoin":
			require(__DIR__ . "/jobs/terracoin.php");
			break;

		case "dogecoin":
			require(__DIR__ . "/jobs/dogecoin.php");
			break;

		case "megacoin":
			require(__DIR__ . "/jobs/megacoin.php");
			break;

		case "ripple":
			require(__DIR__ . "/jobs/ripple.php");
			break;

		case "namecoin":
			require(__DIR__ . "/jobs/namecoin.php");
			break;

		case "litecoin_block":
			require(__DIR__ . "/jobs/litecoin_block.php");
			break;

		case "feathercoin_block":
			require(__DIR__ . "/jobs/feathercoin_block.php");
			break;

		case "ppcoin_block":
			require(__DIR__ . "/jobs/ppcoin_block.php");
			break;

		case "novacoin_block":
			require(__DIR__ . "/jobs/novacoin_block.php");
			break;

		case "primecoin_block":
			require(__DIR__ . "/jobs/primecoin_block.php");
			break;

		case "terracoin_block":
			require(__DIR__ . "/jobs/terracoin_block.php");
			break;

		case "dogecoin_block":
			require(__DIR__ . "/jobs/dogecoin_block.php");
			break;

		case "megacoin_block":
			require(__DIR__ . "/jobs/megacoin_block.php");
			break;

		case "namecoin_block":
			require(__DIR__ . "/jobs/namecoin_block.php");
			break;

		case "generic":
			require(__DIR__ . "/jobs/generic.php");
			break;

		case "bit2c":
			require(__DIR__ . "/jobs/bit2c.php");
			break;

		case "btce":
			require(__DIR__ . "/jobs/btce.php");
			break;

		case "mtgox":
			require(__DIR__ . "/jobs/mtgox.php");
			break;

		case "vircurex":
			require(__DIR__ . "/jobs/vircurex.php");
			break;

		case "poolx":
			require(__DIR__ . "/jobs/poolx.php");
			break;

		case "wemineltc":
			require(__DIR__ . "/jobs/wemineltc.php");
			break;

		case "wemineftc":
			require(__DIR__ . "/jobs/wemineftc.php");
			break;

		case "givemecoins":
			require(__DIR__ . "/jobs/givemecoins.php");
			break;

		case "slush":
			require(__DIR__ . "/jobs/slush.php");
			break;

		case "litecoinglobal":
			require(__DIR__ . "/jobs/litecoinglobal.php");
			break;

		case "securities_litecoinglobal":
			require(__DIR__ . "/jobs/securities_litecoinglobal.php");
			break;

		case "btct":
			require(__DIR__ . "/jobs/btct.php");
			break;

		case "securities_btct":
			require(__DIR__ . "/jobs/securities_btct.php");
			break;

		case "cryptostocks":
			require(__DIR__ . "/jobs/cryptostocks.php");
			break;

		case "securities_cryptostocks":
			require(__DIR__ . "/jobs/securities_cryptostocks.php");
			break;

		case "btcguild":
			require(__DIR__ . "/jobs/btcguild.php");
			break;

		case "hypernova":
			require(__DIR__ . "/jobs/hypernova.php");
			break;

		case "ltcmineru":
			require(__DIR__ . "/jobs/ltcmineru.php");
			break;

		case "miningforeman":
			require(__DIR__ . "/jobs/miningforeman.php");
			break;

		case "miningforeman_ftc":
			require(__DIR__ . "/jobs/miningforeman_ftc.php");
			break;

		case "havelock":
			require(__DIR__ . "/jobs/havelock.php");
			break;

		case "securities_havelock":
			require(__DIR__ . "/jobs/securities_havelock.php");
			break;

		case "bitminter":
			require(__DIR__ . "/jobs/bitminter.php");
			break;

		case "liteguardian":
			require(__DIR__ . "/jobs/liteguardian.php");
			break;

		case "khore":
			require(__DIR__ . "/jobs/khore.php");
			break;

		case "cexio":
			require(__DIR__ . "/jobs/cexio.php");
			break;

		case "ghashio":
			require(__DIR__ . "/jobs/ghashio.php");
			break;

		case "crypto-trade":
			require(__DIR__ . "/jobs/crypto-trade.php");
			break;

		case "bitstamp":
			require(__DIR__ . "/jobs/bitstamp.php");
			break;

		case "796":
			require(__DIR__ . "/jobs/796.php");
			break;

		case "securities_796":
			require(__DIR__ . "/jobs/securities_796.php");
			break;

		case "kattare":
			require(__DIR__ . "/jobs/kattare.php");
			break;

		case "litepooleu":
			require(__DIR__ . "/jobs/litepooleu.php");
			break;

		case "coinhuntr":
			require(__DIR__ . "/jobs/coinhuntr.php");
			break;

		case "eligius":
			require(__DIR__ . "/jobs/eligius.php");
			break;

		case "beeeeer":
			require(__DIR__ . "/jobs/beeeeer.php");
			break;

		case "litecoinpool":
			require(__DIR__ . "/jobs/litecoinpool.php");
			break;

		case "dogepoolpw":
			require(__DIR__ . "/jobs/dogepoolpw.php");
			break;

		case "elitistjerks":
			require(__DIR__ . "/jobs/elitistjerks.php");
			break;

		case "dogechainpool":
			require(__DIR__ . "/jobs/dogechainpool.php");
			break;

		case "hashfaster_ltc":
			require(__DIR__ . "/jobs/hashfaster_ltc.php");
			break;

		case "hashfaster_ftc":
			require(__DIR__ . "/jobs/hashfaster_ftc.php");
			break;

		case "hashfaster_doge":
			require(__DIR__ . "/jobs/hashfaster_doge.php");
			break;

		case "triplemining":
			require(__DIR__ . "/jobs/triplemining.php");
			break;

		case "ozcoin_ltc":
			require(__DIR__ . "/jobs/ozcoin_ltc.php");
			break;

		case "ozcoin_btc":
			require(__DIR__ . "/jobs/ozcoin_btc.php");
			break;

		case "scryptpools":
			require(__DIR__ . "/jobs/scryptpools.php");
			break;

		case "bitcurex_pln":
			require(__DIR__ . "/jobs/bitcurex_pln.php");
			break;

		case "bitcurex_eur":
			require(__DIR__ . "/jobs/bitcurex_eur.php");
			break;

		case "justcoin":
			require(__DIR__ . "/jobs/justcoin.php");
			break;

		case "multipool":
			require(__DIR__ . "/jobs/multipool.php");
			break;

		case "ypool":
			require(__DIR__ . "/jobs/ypool.php");
			break;

		case "coinbase":
			require(__DIR__ . "/jobs/coinbase.php");
			break;

		case "litecoininvest":
			require(__DIR__ . "/jobs/litecoininvest.php");
			break;

		case "btcinve":
			require(__DIR__ . "/jobs/btcinve.php");
			break;

		case "miningpoolco":
			require(__DIR__ . "/jobs/miningpoolco.php");
			break;

		case "vaultofsatoshi":
			require(__DIR__ . "/jobs/vaultofsatoshi.php");
			break;

		case "50btc":
			require(__DIR__ . "/jobs/50btc.php");
			break;

		case "smalltimeminer_mec":
			require(__DIR__ . "/jobs/smalltimeminer_mec.php");
			break;

		case "ecoining_ppc":
			require(__DIR__ . "/jobs/ecoining_ppc.php");
			break;

		case "teamdoge":
			require(__DIR__ . "/jobs/teamdoge.php");
			break;

		case "dedicatedpool_doge":
			require(__DIR__ . "/jobs/dedicatedpool_doge.php");
			break;

		case "nut2pools_ftc":
			require(__DIR__ . "/jobs/nut2pools_ftc.php");
			break;

		// individual securities jobs
		case "individual_litecoinglobal":
			require(__DIR__ . "/jobs/individual_litecoinglobal.php");
			break;

		case "individual_btct":
			require(__DIR__ . "/jobs/individual_btct.php");
			break;

		case "individual_cryptostocks":
			require(__DIR__ . "/jobs/individual_cryptostocks.php");
			break;

		case "individual_havelock":
			require(__DIR__ . "/jobs/individual_havelock.php");
			break;

		case "individual_crypto-trade":
			require(__DIR__ . "/jobs/individual_crypto-trade.php");
			break;

		case "individual_796":
			require(__DIR__ . "/jobs/individual_796.php");
			break;

		case "individual_litecoininvest":
			require(__DIR__ . "/jobs/individual_litecoininvest.php");
			break;

		case "individual_btcinve":
			require(__DIR__ . "/jobs/individual_btcinve.php");
			break;

		// summary jobs
		case "sum":
			require(__DIR__ . "/jobs/sum.php");
			break;

		case "securities_count":
			require(__DIR__ . "/jobs/securities_count.php");
			break;

		// notification jobs
		case "notification":
			require(__DIR__ . "/jobs/notification.php");
			break;

		// system jobs
		case "securities_update":
			require(__DIR__ . "/jobs/securities_update.php");
			break;

		case "version_check":
			require(__DIR__ . "/jobs/version_check.php");
			break;

		// cleanup jobs, admin jobs etc
		case "outstanding":
			require(__DIR__ . "/jobs/outstanding.php");
			break;

		case "expiring":
			require(__DIR__ . "/jobs/expiring.php");
			break;

		case "expire":
			require(__DIR__ . "/jobs/expire.php");
			break;

		case "cleanup":
			require(__DIR__ . "/jobs/cleanup.php");
			break;

		case "disable_warning":
			require(__DIR__ . "/jobs/disable_warning.php");
			break;

		case "disable":
			require(__DIR__ . "/jobs/disable.php");
			break;

		default:
			// issue #12: unsafe accounts
			if (get_site_config('allow_unsafe')) {
				switch ($job['job_type']) {
					case "cryptsy":
						require(__DIR__ . "/jobs/cryptsy.php");
						break 2;

				}
			}

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

// if this is a standard failure-enabled account, then disable the job if it has failed repeatedly,
// or reset the failure count if it's not failed this time
$account_data = false;
foreach (account_data_grouped() as $label => $group) {
	foreach ($group as $exchange => $data) {
		if ($job['job_type'] == $exchange) {
			$account_data = $data;
			$account_data['exchange'] = $exchange;
			break;
		}
	}
}
if (!$account_data) {
	if ($job['job_type'] == 'securities_havelock') {
		$account_data = array('failure' => true, 'table' => 'securities_havelock', 'exchange' => 'securities_havelock', 'label' => 'ticker', 'labels' => 'tickers', 'title' => $job['arg_id']);
	}
}
if ($account_data && $account_data['failure']) {
	$failing_table = $account_data['table'];

	// failed?
	if ($runtime_exception !== null) {
		// don't count CloudFlare as a failure
		if ($runtime_exception instanceof CloudFlareException) {
			crypto_log("Not increasing failure count: was a CloudFlareException");
		} else if ($runtime_exception instanceof IncapsulaException) {
			crypto_log("Not increasing failure count: was a IncapsulaException");
		} else {
			$q = db()->prepare("UPDATE $failing_table SET failures=failures+1,first_failure=IF(ISNULL(first_failure), NOW(), first_failure) WHERE id=?");
			$q->execute(array($job['arg_id']));
			crypto_log("Increasing account failure count");
		}

		$user = get_user($job['user_id']);
		if (!$user) {
			crypto_log("Warning: No user " . $job['user_id'] . " found");

		} else {

			// failed too many times?
			$q = db()->prepare("SELECT * FROM $failing_table WHERE id=? LIMIT 1");
			$q->execute(array($job['arg_id']));
			$account = $q->fetch();
			crypto_log("Current account failure count: " . number_format($account['failures']));

			if ($account['failures'] >= get_premium_value($user, 'max_failures')) {
				// disable it and send an email
				$q = db()->prepare("UPDATE $failing_table SET is_disabled=1 WHERE id=?");
				$q->execute(array($job['arg_id']));

				if ($user['email'] && !$account['is_disabled'] /* don't send the same email multiple times */) {
					send_email($user['email'], ($user['name'] ? $user['name'] : $user['email']), "failure", array(
						"name" => ($user['name'] ? $user['name'] : $user['email']),
						"exchange" => get_exchange_name($account_data['exchange']),
						"label" => $account_data['label'],
						"labels" => $account_data['labels'],
						"failures" => number_format($account['failures']),
						"message" => $runtime_exception->getMessage(),
						"length" => recent_format(strtotime($account['first_failure']), "", ""),
						"title" => (isset($account['title']) && $account['title']) ? "\"" . $account['title'] . "\"" : "untitled",
						"url" => absolute_url(url_for("wizard_accounts")),
					));
					crypto_log("Sent failure e-mail to " . htmlspecialchars($user['email']) . ".");
				}

			}

		}

	} else {

		// reset the failure counter
		$q = db()->prepare("UPDATE $failing_table SET failures=0 WHERE id=?");
		$q->execute(array($job['arg_id']));

	}
}

if (defined('BATCH_JOB_START')) {
	$end_time = microtime(true);
	$time_diff = ($end_time - BATCH_JOB_START) * 1000;
	crypto_log("Executed in " . number_format($time_diff, 2) . " ms.");
}

// rethrow exception if necessary
if ($runtime_exception !== null) {
	throw new WrappedJobException($runtime_exception, $job['id']);
}

echo "\n<li>Job successful.";

batch_footer();

function insert_new_balance($job, $account, $exchange, $currency, $balance) {

	crypto_log("$exchange $currency balance for user " . $job['user_id'] . ": " . $balance);

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
	$last_id = db()->lastInsertId();
	crypto_log("Inserted new $exchange $currency balances id=" . $last_id);

	// disable old instances
	$q = db()->prepare("UPDATE balances SET is_recent=0 WHERE is_recent=1 AND user_id=:user_id AND exchange=:exchange AND account_id=:account_id AND currency=:currency AND id <> :id");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
		"id" => $last_id,
	));

	// all other data from today is now old
	// NOTE if the system time changes between the next two commands, then we may erraneously
	// specify that there is no valid daily data. one solution is to specify NOW() as $created_at rather than
	// relying on MySQL
	$q = db()->prepare("UPDATE balances SET is_daily_data=0 WHERE is_daily_data=1 AND user_id=:user_id AND account_id=:account_id AND exchange=:exchange AND currency=:currency AND
		date_format(created_at, '%d-%m-%Y') = date_format(now(), '%d-%m-%Y') AND id <> :id");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
		"id" => $last_id,
	));


}

function insert_new_hashrate($job, $account, $exchange, $currency, $mhash) {

	crypto_log("$exchange $currency hashrate for user " . $job['user_id'] . ": " . $mhash . " MH/s");

	// we have a balance; update the database
	$q = db()->prepare("INSERT INTO hashrates SET user_id=:user_id, exchange=:exchange, account_id=:account_id, mhash=:mhash, currency=:currency, job_id=:job_id, is_recent=1, is_daily_data=1");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
		"mhash" => $mhash,
		"job_id" => $job['id'],
		// we ignore server_time
	));
	$last_id = db()->lastInsertId();
	crypto_log("Inserted new $exchange $currency hashrates id=" . $last_id);

	// disable old instances
	$q = db()->prepare("UPDATE hashrates SET is_recent=0 WHERE is_recent=1 AND user_id=:user_id AND exchange=:exchange AND account_id=:account_id AND currency=:currency AND id <> :id");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
		"id" => $last_id,
	));

	// all other data from today is now old
	// NOTE if the system time changes between the next two commands, then we may erraneously
	// specify that there is no valid daily data. one solution is to specify NOW() as $created_at rather than
	// relying on MySQL
	$q = db()->prepare("UPDATE hashrates SET is_daily_data=0 WHERE is_daily_data=1 AND user_id=:user_id AND account_id=:account_id AND exchange=:exchange AND currency=:currency AND
		date_format(created_at, '%d-%m-%Y') = date_format(now(), '%d-%m-%Y') AND id <> :id");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
		"id" => $last_id,
	));

}

function add_summary_instance($job, $summary_type, $total) {

	// insert new summary
	$q = db()->prepare("INSERT INTO summary_instances SET is_recent=1, user_id=:user_id, summary_type=:summary_type, balance=:balance, job_id=:job_id, is_daily_data=1");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"summary_type" => $summary_type,
		"balance" => $total,
		"job_id" => $job['id'],
	));
	$last_id = db()->lastInsertId();
	crypto_log("Inserted new summary_instances '$summary_type' id=" . $last_id);

	// update old summaries
	$q = db()->prepare("UPDATE summary_instances SET is_recent=0 WHERE is_recent=1 AND user_id=? AND summary_type=? AND id <> ?");
	$q->execute(array($job['user_id'], $summary_type, $last_id));

	// all other data from today is now old
	// NOTE if the system time changes between the next two commands, then we may erraneously
	// specify that there is no valid daily data. one solution is to specify NOW() as $created_at rather than
	// relying on MySQL
	$q = db()->prepare("UPDATE summary_instances SET is_daily_data=0 WHERE is_daily_data=1 AND summary_type=:summary_type AND user_id=:user_id AND
		date_format(created_at, '%d-%m-%Y') = date_format(now(), '%d-%m-%Y') AND id <> :id");
	$q->execute(array(
		"summary_type" => $summary_type,
		"user_id" => $job['user_id'],
		"id" => $last_id,
	));

}

/**
 * Try to decode a JSON string, or try and work out why it failed to decode but throw an exception
 * if it was not a valid JSON string.
 *
 * @param empty_is_ok if true, then don't bail if the returned JSON is an empty array
 */
function crypto_json_decode($string, $message = false, $empty_array_is_ok = false) {
	$json = json_decode($string, true);
	if (!$json) {
		if ($empty_array_is_ok && is_array($json)) {
			// the result is an empty array
			return $json;
		}
		crypto_log(htmlspecialchars($string));
		if (strpos($string, 'DDoS protection by CloudFlare') !== false) {
			throw new CloudFlareException('Throttled by CloudFlare' . ($message ? " $message" : ""));
		}
		if (strpos($string, 'CloudFlare') !== false) {
			if (strpos($string, 'The origin web server timed out responding to this request.') !== false) {
				throw new CloudFlareException('Cloudflare reported: The origin web server timed out responding to this request.');
			}
		}
		if (strpos($string, 'Incapsula incident') !== false) {
			throw new IncapsulaException('Blocked by Incapsula' . ($message ? " $message" : ""));
		}
		if (strpos($string, '_Incapsula_Resource') !== false) {
			throw new IncapsulaException('Throttled by Incapsula' . ($message ? " $message" : ""));
		}
		if (strpos(strtolower($string), '301 moved permanently') !== false) {
			throw new ExternalAPIException("API location has been moved permanently" . ($message ? " $message" : ""));
		}
		if (strpos($string, "Access denied for user '") !== false) {
			throw new ExternalAPIException("Remote database host returned 'Access denied'" . ($message ? " $message" : ""));
		}
		if (strpos(strtolower($string), "502 bad gateway") !== false) {
			throw new ExternalAPIException("Bad gateway" . ($message ? " $message" : ""));
		}
		if (substr($string, 0, 1) == "<") {
			throw new ExternalAPIException("Unexpectedly received HTML instead of JSON" . ($message ? " $message" : ""));
		}
		if (strpos(strtolower($string), "invalid key") !== false) {
			throw new ExternalAPIException("Invalid key" . ($message ? " $message" : ""));
		}
		if (strpos(strtolower($string), "bad api key") !== false) {
			throw new ExternalAPIException("Bad API key" . ($message ? " $message" : ""));
		}
		if (strpos(strtolower($string), "access denied") !== false) {
			throw new ExternalAPIException("Access denied" . ($message ? " $message" : ""));
		}
		if (strpos(strtolower($string), "parameter error") !== false) {
			// for 796 Exchange
			throw new ExternalAPIException("Parameter error" . ($message ? " $message" : ""));
		}
		if (!$string) {
			throw new EmptyResponseException('Response was empty' . ($message ? " $message" : ""));
		}
		throw new ExternalAPIException('Invalid data received' . ($message ? " $message" : ""));
	}
	return $json;
}


/**
 * @param $values array(last_trade, bid, ask, volume (optional))
 */
function insert_new_ticker($job, $exchange, $cur1, $cur2, $values) {

	// sanity and quality checks
	if (!isset($values['last_trade'])) {
		throw new Exception("No last_trade specified");	// need at least this
	}
	if (isset($values['sell'])) {
		throw new Exception("Invalid parameter: sell (should be bid)");
	}
	if (isset($values['buy'])) {
		throw new Exception("Invalid parameter: buy (should be ask)");
	}
	if (!isset($values['volume'])) {
		$values['volume'] = null;
	}
	if (!isset($values['bid'])) {
		$values['bid'] = null;
	}
	if (!isset($values['ask'])) {
		$values['ask'] = null;
	}
	if (strlen($exchange['name']) <= 1) {
		throw new Exception("Invalid parameter: exchange '" . htmlspecialchars($exchange['name']) . "'");
	}
	if (strlen($cur1) <= 1) {
		throw new Exception("Invalid parameter: currency1 '" . htmlspecialchars($cur1) . "'");
	}
	if (strlen($cur2) <= 1) {
		throw new Exception("Invalid parameter: currency2 '" . htmlspecialchars($cur2) . "'");
	}

	crypto_log($exchange['name'] . " rate for $cur1/$cur2: " . $values['last_trade'] . " (" . $values['bid'] . "/" . $values['ask'] . ")");
	if ($values['bid'] > $values['ask']) {
		crypto_log("<strong>WARNING:</strong> bid > ask");
	}

	// insert in new ticker value
	$q = db()->prepare("INSERT INTO ticker SET exchange=:exchange, currency1=:currency1, currency2=:currency2, last_trade=:last_trade, bid=:bid, ask=:ask, volume=:volume, job_id=:job_id, is_daily_data=1");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => $cur1,
		"currency2" => $cur2,
		"last_trade" => $values['last_trade'],
		/*
		 * The 'bid' price is the highest price that a buyer is willing to pay (i.e. the 'sell');
		 * the 'ask' price is the lowest price that a seller is willing to sell (i.e. the 'buy').
		 * Therefore bid <= ask, buy <= sell.
		 */
		"bid" => $values['bid'],
		"ask" => $values['ask'],
		"volume" => $values['volume'],
		"job_id" => $job['id'],
	));

	$last_id = db()->lastInsertId();
	crypto_log("Inserted new ticker id=" . $last_id);

	// put into the most recent table
	// TODO could also use a REPLACE statement
	$q = db()->prepare("SELECT * FROM ticker_recent WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 LIMIT 1");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => $cur1,
		"currency2" => $cur2,
	));
	if (!$q->fetch()) {
		// insert in a new blank value (this will not occur very often)
		$q = db()->prepare("INSERT INTO ticker_recent SET exchange=:exchange, currency1=:currency1, currency2=:currency2");
		$q->execute(array(
			"exchange" => $exchange['name'],
			"currency1" => $cur1,
			"currency2" => $cur2,
		));
	}

	// update the previously existing recent value
	$q = db()->prepare("UPDATE ticker_recent SET created_at=NOW(), last_trade=:last_trade, bid=:bid, ask=:ask, volume=:volume, job_id=:job_id
			WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2");
	$q->execute(array(
		"last_trade" => $values['last_trade'],
		"bid" => $values['bid'],
		"ask" => $values['ask'],
		"volume" => $values['volume'],
		"job_id" => $job['id'],
		"exchange" => $exchange['name'],
		"currency1" => $cur1,
		"currency2" => $cur2,
	));

	// all other data from today is now old
	// NOTE if the system time changes between the next two commands, then we may erraneously
	// specify that there is no valid daily data. one solution is to specify NOW() as $created_at rather than
	// relying on MySQL
	$q = db()->prepare("UPDATE ticker SET is_daily_data=0 WHERE is_daily_data=1 AND exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND
		date_format(created_at, '%d-%m-%Y') = date_format(now(), '%d-%m-%Y') AND id <> :id");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => $cur1,
		"currency2" => $cur2,
		"id" => $last_id,
	));

}
