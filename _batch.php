<?php

/**
 * Various batch-related helper functions.
 */
define('BATCH_SCRIPT', true);

function require_batch_key() {
	global $argv;
	if (!(isset($argv) && $argv[1] == get_site_config("automated_key")) && require_get("key") != get_site_config("automated_key"))
		throw new Exception("Invalid key");
}

function batch_header($page_name, $page_id) {
	if (require_get("key", false)) {
		// we're running from a web browser
		require(__DIR__ . "/layout/templates.php");
		$options = array();
		if (require_get("refresh", false)) {
			$options["refresh"] = require_get("refresh");
		}
		page_header($page_name, $page_id, $options);
	}
}

function batch_footer() {
if (require_get("key", false)) {
		// we're running from a web browser
		// include page gen times etc
		page_footer();
	}
}

class JobException extends Exception { }
function crypto_log($log) {
	echo "\n<li>$log</li>";
	// flush();
}
class ExternalAPIException extends Exception { } // expected exceptions
class EmptyResponseException extends ExternalAPIException { } // expected exception; allows us to handle e.g BitMinter
class CloudFlareException extends ExternalAPIException { } // expected exception; TODO implement some code to handle CloudFlare blocking
class IncapsulaException extends ExternalAPIException { } // expected exception; TODO implement some code to handle Incapsula blocking
function crypto_wrap_url($url) {
	// remove API keys etc
	$url_clean = $url;
	$url_clean = preg_replace('#key=([^&]{3})[^&]+#im', 'key=\\1...', $url_clean);
	$url_clean = preg_replace('#hash=([^&]{3})[^&]+#im', 'hash=\\1...', $url_clean);
	crypto_log("Requesting <a href=\"" . htmlspecialchars($url_clean) . "\">" . htmlspecialchars($url_clean) . "</a>...");
	return $url;
}
// wraps file_get_contents() with timeout information etc
function crypto_get_contents($url, $options = array()) {
	// normally file_get_contents is OK, but if URLs are down etc, the timeout has no value and we can just stall here forever
	// this also means we don't have to enable OpenSSL on windows (etc), which is just a bit of a mess
	$ch = null;
	if (is_null($ch)) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Openclerk PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	}
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, get_site_config('get_contents_timeout') /* in sec */);	// defaults to infinite
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, get_site_config('get_contents_timeout') /* in sec */);	// defaults to 300s
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	foreach ($options as $key => $value) {
		curl_setopt($ch, $key, $value);
	}

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
