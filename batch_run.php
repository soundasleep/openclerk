<?php

/**
 * Batch script: find a job to execute, and then execute it.
 * We don't use meta-jobs to insert in new jobs, because we implement
 * job priority and we don't want to have to also use 'execute_after'.
 */

if (!defined('ADMIN_RUN_JOB')) {
	require("inc/global.php");
}
require("_batch.php");

require_batch_key();
batch_header("Batch run", "batch_run");

if (require_get("job_id", false)) {
	// run a particular job, even if it's already been executed
	$q = db()->prepare("SELECT * FROM jobs WHERE id=?");
	$q->execute(array(require_get("job_id")));
	$job = $q->fetch();
} else {
	// select the most important job to execute next
	$q = db()->prepare("SELECT * FROM jobs WHERE is_executed=0 ORDER BY priority ASC, id ASC LIMIT 1");
	$q->execute();
	$job = $q->fetch();
}

if (!$job) {
	// nothing to do!
	echo "No job to execute.";
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
$q = db()->prepare("UPDATE jobs SET is_executed=1,is_error=?,executed_at=NOW() WHERE id=? LIMIT 1");
$q->execute(array(($runtime_exception === null ? 0 : 1), $job['id']));

// rethrow exception if necessary
if ($runtime_exception !== null) {
	throw new WrappedJobException($runtime_exception, $job['id']);
}

echo "\n<li>Job successful.";

batch_footer();
