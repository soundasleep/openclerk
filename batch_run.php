<?php

/**
 * Batch script: find a job to execute, and then execute it.
 * We don't use meta-jobs to insert in new jobs, because we implement
 * job priority and we don't want to have to also use 'execute_after'.
 */

require("inc/global.php");

if (!(isset($argv) && $argv[1] == get_site_config("automated_key")) && require_get("key") != get_site_config("automated_key"))
	throw new Exception("Invalid key");

// select the most important job to execute next
$q = db()->prepare("SELECT * FROM jobs WHERE is_executed=0 ORDER BY priority ASC, id ASC");
$q->execute();
$job = $q->fetch();

if (!$job) {
	// nothing to do!
	echo "No job to execute.";
	return;
}

class JobException extends Exception { }
function crypto_log($log) {
	echo "\n<li>$log</li>";
}
class ExternalAPIException extends Exception { } // expected exceptions
function crypto_wrap_url($url) {
	// remove API keys etc
	$url_clean = $url;
	$url_clean = preg_replace('#key=([^&]{3})[^&]+#im', 'key=\\1...', $url_clean);
	$url_clean = preg_replace('#hash=([^&]{3})[^&]+#im', 'hash=\\1...', $url_clean);
	crypto_log("Requesting <a href=\"" . htmlspecialchars($url_clean) . "\">" . htmlspecialchars($url_clean) . "</a>...");
	return $url;
}

// otherwise, we'll want to actually execute something, based on the job type
$runtime_exception = null;
try {
	switch ($job['job_type']) {
		// ticker jobs
		case "ticker-btce":
			require("jobs/ticker-btce.php");
			break;

		case "ticker-bitnz":
			require("jobs/ticker-bitnz.php");
			break;

		// address jobs
		case "blockchain":
			require("jobs/blockchain.php");
			break;

		case "generic":
			require("jobs/generic.php");
			break;

		case "summary":
			require("jobs/summary.php");
			break;

		// cleanup jobs, admin jobs etc

	}
} catch (Exception $e) {
	// if an exception occurs, we still want to remove the job from the queue, even though we
	// may not have inserted in any valid data
	$runtime_exception = $e;
}

// delete job
$q = db()->prepare("UPDATE jobs SET is_executed=1 WHERE id=? LIMIT 1");
$q->execute(array($job['id']));

// rethrow exception if necessary
if ($runtime_exception !== null) {
	throw $runtime_exception;
}

echo "Job " . htmlspecialchars(print_r($job, true)) . " successful.";
