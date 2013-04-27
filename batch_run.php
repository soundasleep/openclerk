<?php

/**
 * Batch script: find a job to execute, and then execute it.
 * We don't use meta-jobs to insert in new jobs, because we implement
 * job priority and we don't want to have to also use 'execute_after'.
 */

require("inc/global.php");

if (!(isset($argv) && $argv[1] == get_site_config("automated_key")) && require_get("key") != get_site_config("automated_key"))
	throw new Exception("Invalid key");

if (require_get("key", false)) {
	// we're running from a web browser
	require("layout/templates.php");
	page_header("Run", "page_batch_run");
}

if (require_get("job_id", false)) {
	// run a particular job, even if it's already been executed
	$q = db()->prepare("SELECT * FROM jobs WHERE id=?");
	$q->execute(array(require_get("job_id")));
	$job = $q->fetch();
} else {
	// select the most important job to execute next
	$q = db()->prepare("SELECT * FROM jobs WHERE is_executed=0 ORDER BY priority ASC, id ASC");
	$q->execute();
	$job = $q->fetch();
}

if (!$job) {
	// nothing to do!
	echo "No job to execute.";
	return;
}

class JobException extends Exception { }
function crypto_log($log) {
	echo "\n<li>$log</li>";
	// flush();
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
// wraps file_get_contents() with timeout information etc
function crypto_get_contents($url) {
	// normally file_get_contents is OK, but if URLs are down etc, the timeout has no value and we can just stall here forever
	// this also means we don't have to enable OpenSSL on windows (etc), which is just a bit of a mess
	$ch = null;
	if (is_null($ch)) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Openclerk PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	}
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, get_site_config('get_contents_timeout') * 1000);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	// run the query
	$res = curl_exec($ch);
	if ($res === false) throw new ExternalAPIException('Could not get reply: '.curl_error($ch));

	return $res;

	// disabled
	$context = stream_context_create(array(
		'http' => array('timeout' => get_site_config('get_contents_timeout')),
		'https' => array('timeout' => get_site_config('get_contents_timeout')),
	));
	return file_get_contents($url, false /* $use_include_path */, $context);
}
class WrappedJobException extends Exception {
	public $job_id;
	public $cause;
	public function __construct($cause, $job_id) {
		parent::__construct($cause->getMessage());
		$this->cause = $cause;
		$this->job_id = $job_id;
	}
	public function getCause() {
		return $this->cause;
	}
	public function getJobId() {
		return $this->job_id;
	}
}

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

		case "generic":
			require("jobs/generic.php");
			break;

		case "btce":
			require("jobs/btce.php");
			break;

		case "mtgox":
			require("jobs/mtgox.php");
			break;

		case "poolx":
			require("jobs/poolx.php");
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
