<?php

/*
require(__DIR__ . (disabled) "/locale.php");
require(__DIR__ . (disabled) "/standard.php");
*/
require(__DIR__ . "/config.php");
require(__DIR__ . "/security.php");
require(__DIR__ . "/email.php");
require(__DIR__ . "/recaptcha.php");
require(__DIR__ . "/crypto.php");
require(__DIR__ . "/premium.php");
require(__DIR__ . "/heavy.php");
require(__DIR__ . "/kb.php");

$db_instance = false;
function db() {
	global $db_instance;
	if (!$db_instance) {
		$db_instance = new PDO(get_site_config('database_url'), get_site_config('database_username'), get_site_config('database_password'));
		if (get_site_config('timed_sql')) {
			$db_instance = new DebugPDOWrapper($db_instance);
		}
		$db_instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// set timezone
		if (get_site_config('database_timezone', false)) {
			$q = db()->prepare("SET time_zone=?");
			$q->execute(array(get_site_config('database_timezone')));	// TODO make get_site_config parameter
		}
	}
	return $db_instance;
}

if (get_site_config('timed_sql')) {
	$global_timed_sql = array(
		'setAttribute' => array('count' => 0, 'time' => 0),
		'prepare' => array('count' => 0, 'time' => 0),
		'execute' => array('count' => 0, 'time' => 0),
		'fetch' => array('count' => 0, 'time' => 0),
		'fetchAll' => array('count' => 0, 'time' => 0),
		'lastInsertId' => array('count' => 0, 'time' => 0),
		'queries' => array(),
	);
}

// query statistics
$stats_queries = 0;
$stats_fetch = 0;
$stats_fetchAll = 0;

/**
 * Wraps arbitrary PDO objects and passes along methods, arguments etc.
 */
class DebugPDOWrapper {
	var $wrap;

	public function __construct($wrap) {
		$this->wrap = $wrap;
	}

	public function setAttribute($a, $b) {
		global $global_timed_sql;
		$start_time = microtime(true);
		$result = $this->wrap->setAttribute($a, $b);
		$end_time = microtime(true);
		$time_diff = ($end_time - $start_time) * 1000;
		$global_timed_sql['setAttribute']['count']++;
		$global_timed_sql['setAttribute']['time'] += $time_diff;
		return $result;
	}

	var $query = false;

	public function prepare($a) {
		global $global_timed_sql;
		$start_time = microtime(true);
		$result = new DebugPDOWrapper($this->wrap->prepare($a));
		$result->query = $a;
		$end_time = microtime(true);
		$time_diff = ($end_time - $start_time) * 1000;
		$global_timed_sql['prepare']['count']++;
		$global_timed_sql['prepare']['time'] += $time_diff;
		if (!isset($global_timed_sql['queries'][$a])) {
			$global_timed_sql['queries'][$a] = array(
				'count' => 0,
				'time' => 0,
			);
		}
		return $result;
	}

	public function execute($a = array()) {
		global $global_timed_sql;
		$start_time = microtime(true);
		$result = $this->wrap->execute($a);
		$end_time = microtime(true);
		$time_diff = ($end_time - $start_time) * 1000;
		$global_timed_sql['execute']['count']++;
		$global_timed_sql['execute']['time'] += $time_diff;
		if (isset($global_timed_sql['queries'][$this->query])) {
			$global_timed_sql['queries'][$this->query]['count']++;
			$global_timed_sql['queries'][$this->query]['time'] += $time_diff;
		}
		global $stats_queries;
		$stats_queries++;
		return $result;
	}

	public function fetch() {
		global $global_timed_sql;
		$start_time = microtime(true);
		$result = $this->wrap->fetch();
		$end_time = microtime(true);
		$time_diff = ($end_time - $start_time) * 1000;
		$global_timed_sql['fetch']['count']++;
		$global_timed_sql['fetch']['time'] += $time_diff;
		global $stats_fetch;
		$stats_fetch++;
		return $result;
	}

	public function fetchAll() {
		global $global_timed_sql;
		$start_time = microtime(true);
		$result = $this->wrap->fetchAll();
		$end_time = microtime(true);
		$time_diff = ($end_time - $start_time) * 1000;
		$global_timed_sql['fetchAll']['count']++;
		$global_timed_sql['fetchAll']['time'] += $time_diff;
		global $stats_fetchAll;
		$stats_fetchAll++;
		return $result;
	}

	public function lastInsertId() {
		global $global_timed_sql;
		$start_time = microtime(true);
		$result = $this->wrap->lastInsertId();
		$end_time = microtime(true);
		$time_diff = ($end_time - $start_time) * 1000;
		$global_timed_sql['lastInsertId']['count']++;
		$global_timed_sql['lastInsertId']['time'] += $time_diff;
		return $result;
	}

	public function rowCount() {
		// just pass it on, don't time it
		return $this->wrap->rowCount();
	}

	/**
	 * Return a string of current (relevant) stats, and reset these statistics count.
	 */
	public function stats() {
		global $stats_queries, $stats_fetch, $stats_fetchAll;
		$s = number_format($stats_queries) . " queries" . ($stats_fetch ? ", " . number_format($stats_fetch) . " fetch" : "") . ($stats_fetchAll ? ", " . number_format($stats_fetchAll) . " fetchAll" : "");
		$stats_queries = $stats_fetch = $stats_fetchAll = 0;
		return $s;
	}

}

function require_get($key, $default = null) {
	if (isset($_GET[$key])) {
		return $_GET[$key];
	} else if ($default !== null) {
		return $default;
	} else {
		throw new Exception("Required get parameter '$key' not available");
	}
}

function require_post($key, $default = null) {
	if (isset($_POST[$key])) {
		return $_POST[$key];
	} else if ($default !== null) {
		return $default;
	} else {
		throw new Exception("Required post parameter '$key' not available");
	}
}

function require_session($key, $default = null) {
	if (isset($_SESSION[$key])) {
		return $_SESSION[$key];
	} else if ($default !== null) {
		return $default;
	} else {
		throw new Exception("Required session parameter '$key' not available");
	}
}

function print_exception_trace($e) {
	if (!$e) {
		echo "<code>null</code>\n";
		return;
	}
	if (!($e instanceof Exception)) {
		echo "<i>Not exception: " . get_class($e) . ": " . print_r($e, true) . "</i>";
		return;
	}
	echo "<ul>";
	echo "<li><b>" . htmlspecialchars($e->getMessage()) . "</b> (<i>" . get_class($e) . "</i>)</li>\n";
	echo "<li>" . htmlspecialchars($e->getFile()) . "#" . htmlspecialchars($e->getLine()) . "</li>\n";
	foreach ($e->getTrace() as $e2) {
		echo "<li>" . htmlspecialchars($e2['file']) . "#" . htmlspecialchars($e2['line']) . ": " . htmlspecialchars($e2['function']) . htmlspecialchars(isset($e2['args']) ? format_args_list($e2['args']) : "") . "</li>\n";
	}
	if ($e->getPrevious()) {
		echo "<li>Caused by:";
		print_exception_trace($e->getPrevious());
		echo "</li>";
	}
	echo "</ul>";
}
function format_args_list($a, $count = 0) {
	if (is_array($a)) {
		$data = array();
		$i = 0;
		foreach ($a as $key => $value) {
			if ($i++ >= 3) {
				$data[] = "..."; break;
			}
			$data[$key] = format_args_list($value);
		}
		return "(" . implode(",", $data) . ")";
	}
	return $a;
}
function my_exception_handler($e) {
	$extra_args = array();
	$extra_query = "";
	if ($e instanceof WrappedJobException) {
		// unwrap it
		$extra_args[] = $e->getJobId();
		$extra_query .= ", job_id=?";
		$e = $e->getCause();
	}

	header('HTTP/1.0 500 Internal Server Error');
	echo "Error: " . htmlspecialchars($e->getMessage());
	if ($_SERVER['SERVER_NAME'] === 'localhost') {
		// only display trace locally
		echo "<br>Trace:";
		print_exception_trace($e);
	}
	// logging
	log_uncaught_exception($e, $extra_args, $extra_query);
	die;
}
set_exception_handler('my_exception_handler');
function log_uncaught_exception($e, $extra_args = array(), $extra_query = "") {
	// logging
	if (get_class($e) !== false) {
		$extra_args[] = get_class($e);
		$extra_query .= ", class_name=?";
	}
	$q = db()->prepare("INSERT INTO uncaught_exceptions SET
		message=?,
		previous_message=?,
		filename=?,
		line_number=?,
		raw=?,
		created_at=NOW() $extra_query");
	$q->execute(array_join(array(
		// clamp messages to 255 characters
		substr($e->getMessage(), 0, 255),
		substr($e->getPrevious() ? $e->getPrevious()->getMessage() : "", 0, 255),
		substr($e->getFile(), 0, 255),
		$e->getLine(),
		serialize($e),
	), $extra_args));
}

function redirect($url) {
	header('Location: ' . $url);
	die();
}

/**
 * Return an absolute URL for a page on the current site.
 */
function absolute_url($url) {
	return get_site_config('absolute_url') . $url;
}

function xml_header() {
	header("Content-Type: text/xml");
	echo "<" . "?" . "xml version=\"1.0\"" . "?" . ">\n";
}

function iso_date($date = null) {
	if ($date == null)
		return date('c');
	elseif (is_numeric($date))
		return date('c', $date);
	else
		return date('c', strtotime($date));
}

/**
 * Format the given date in a format suitable for MySQL. If null, returns the current date.
 */
function db_date($date = null) {
	$format = 'Y-m-d H:i:s'; 	// 2010-01-01 01:01:01, i.e. no timezone data. TODO assumes that the database is in the same timezone as the app
	if ($date == null)
		return date($format);
	elseif (is_numeric($date))
		return date($format, $date);
	else
		return date($format, strtotime($date));
}

function array_join($a1, $a2) {
	if (!is_array($a2))
		throw new InvalidArgumentException("Argument '$a2' is not an array");

	foreach ($a2 as $value) {
		$a1[] = $value;
	}
	return $a1;
}

/**
 * Returns {@code true} if the two arrays have the same values, in any order.
 * @param $strict if {@code true}, then search will be via identity (===)
 */
function array_equals($a, $b, $strict = false) {
	foreach ($a as $aa) {
		if (($key = array_search($aa, $b, $strict)) !== false) {
			unset($b[$key]);
		} else {
			return false;	// we found a key in $a that isn't in $b
		}
	}
	if (!$b) {
		// all of $b was in $a, so the arrays are equal
		return true;
	} else {
		return false;
	}
}

function recent_format($date = null, $suffix = false, $future_suffix = false) {
	if ($suffix === false) $suffix = " ago";
	if ($future_suffix === false) $future_suffix = " in the future";

	if ($date == null || $date == 0)
		return "<em>never</em>";

	if (!is_numeric($date))
		$date = strtotime($date);

	$secs = time() - $date;
	if ($secs < 0)
		return seconds_to_string(-$secs, $future_suffix);
	else
		return seconds_to_string($secs, $suffix);
}

function seconds_to_string($secs, $suffix = " ago") {
	if ($secs == 0)
		return "<em>now</em>";
	else if ($secs < 60)
		return plural(number_format($secs), "sec", "sec") . $suffix;
	else if ($secs < 60 * 60)
		return plural(number_format($secs / 60), "min", "min") . $suffix;
	else if ($secs < (60 * 60 * 24))
		return plural(number_format($secs / (60 * 60)), "hour", "hours") . $suffix;
	else if ($secs < (60 * 60 * 24 * 31))
		return plural(number_format($secs / (60 * 60 * 24)), "day", "days") . $suffix;
	else if (year_count($secs) < 1)
		return plural(number_format($secs / (60 * 60 * 24 * (365.242/12))), "month", "months") . $suffix;
	else
		return plural(number_format(year_count($secs), 1), "year", "years") . $suffix;
}

function plural($n, $s, $ss = false) {
	if ($ss === false) $ss = $s . "s";
	if ($n == 1) {
		return sprintf("%s %s", $n, $s);
	} else {
		return sprintf("%s %s", $n, $ss);
	}
}

function recent_format_html($date, $suffix = false, $future_suffix = false) {
	return '<span title="' . ($date ? htmlspecialchars(iso_date($date)) : "Never") . '">' . recent_format($date, $suffix, $future_suffix) . '</span>';
}

function expected_delay_html($minutes) {
	if ($minutes == 0) {
		return "<i>none</i>";
	} else if ($minutes < 60) {
		return "&lt; " . plural(ceil($minutes), "min");
	} else if ($minutes < (60 * 60)) {
		return "&lt; " . plural(ceil($minutes / 60), "hour");
	} else {
		return "&lt; " . plural(ceil($minutes / (60 * 60)), "day");
	}
}

function year_count($sec) {
	return $sec / (60 * 60 * 24 * 365.242);
}

/**
 * Translates an array into e.g.:
 *   'a'
 *   'a and b'
 *   'a, b and c'
 *   'a, b, c and d'
 */
function implode_english($result, $or = false) {
	$s = "";
	for ($i = 0; $i < count($result) - 2; $i++) {
		$s .= $result[$i] . ", ";
	}
	for ($i = count($result) - 2; $i >= 0 && $i < count($result) - 1; $i++) {
		$s .= $result[$i] .
			((count($result) > 2 && strpos($result[$i], " ")) !== false ? "," : "") . // for phrased terms and long lists, add an extra comma
			" " . ($or ? "or" : "and") . " ";
	}
	for ($i = count($result) - 1; $i >= 0 && $i < count($result); $i++) {
		$s .= $result[$i];
	}
	return $s;
}

function capitalize($s) {
        $split = explode(" ", $s);
        foreach ($split as $i => $value) {
                $split[$i] = strtoupper(substr($value, 0, 1)) . substr($value, 1);
        }
        return implode(" ", $split);
}

/**
 * Wrap the given number to the given number of decimal places.
 * Probably returns 0 if this is not a number.
 */
function wrap_number($n, $dp) {
	return number_format($n, $dp, ".", "");
}

/**
 * Escape the given XML string.
 */
function xmlescape($str) {
	// TODO implement
	return $str;
}

/**
 * Display an XML error.
 */
function display_xml_error($e) {
	xml_header();
?>
<error time="<?php echo iso_date(); ?>">
<?php echo xmlescape($e->getMessage()); ?>
</error>
	<?php
	die();
}

/**
 * Can be cached.
 */
$global_calculate_relative_path = null;
function calculate_relative_path() {
	global $global_calculate_relative_path;
	if ($global_calculate_relative_path === null) {
		// construct a relative path for this request based on the request URI, but only if it is set
		if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI']) {
			$uri = $_SERVER['REQUEST_URI'];
			// strip out the hostname from the absolute_url
			$intended = substr(get_site_config('absolute_url'), strpos(get_site_config('absolute_url'), '://') + 4);
			$intended = substr($intended, strpos($intended, '/'));
			// if we're in this path, remove it
			// now generate ../s as necessary
			if (strtolower(substr($uri, 0, strlen($intended))) == strtolower($intended)) {
				$uri = substr($uri, strlen($intended));
			}
			// but strip out any parameters, which might have /s in them, which will completely mess this up
			// (see issue #13)
			if (strpos($uri, "?") !== false) {
				$uri = substr($uri, 0, strpos($uri, "?"));
			}
			$global_calculate_relative_path = str_repeat('../', substr_count($uri, '/'));
		} else {
			$global_calculate_relative_path = "";
		}
	}
	return $global_calculate_relative_path;
}

/**
 * Generate the url for a particular module (i.e. script) and particular arguments (i.e. query string elements).
 * Handles relative paths back to the root, but /clerk/foo/bar to /clerk/bar/foo is untested.
 * Also handles #hash arguments.
 * Should handle absolute arguments OK.
 */
function url_for($module, $arguments = array()) {
	$is_absolute = (strpos($module, "://") !== false);
	$hash = false;
	if (strpos($module, "#") !== false) {
		$hash = substr($module, strpos($module, "#") + 1);
		$module = substr($module, 0, strpos($module, "#"));
	}
	// rewrite e.g. help?kb=foo to help/foo
	switch ($module) {
		case "kb":
			if (isset($arguments['q'])) {
				$module = 'help/' . urlencode($arguments['q']);
				unset($arguments['q']);
			}
			break;
		case "index":
			$module = ".";
			break;
	}
	$query = array();
	if (count($arguments) > 0) {
		foreach ($arguments as $key => $value) {
			$query[] = urlencode($key) . "=" . urlencode($value);
		}
	}
	return ($is_absolute ? "" : calculate_relative_path()) . $module . /* ".php" . */ (count($query) ? "?" . implode("&", $query) : "") . ($hash ? "#" . $hash : "");
}

/**
 * Add GET arguments onto a particular URL. Does not replace any existing arguments.
 * Also handles #hash arguments.
 */
function url_add($url, $arguments) {
	$hash = false;
	if (strpos($url, "#") !== false) {
		$hash = substr($url, strpos($url, "#") + 1);
		$url = substr($url, 0, strpos($url, "#"));
	}
	foreach ($arguments as $key => $value) {
		if (strpos($url, "?") !== false) {
			$url .= "&" . urlencode($key) . "=" . urlencode($value);
		} else {
			$url .= "?" . urlencode($key) . "=" . urlencode($value);
		}
	}
	if ($hash) {
		$url .= "#" . $hash;
	}
	return $url;
}

function request_url() {
	return ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https" : "http") . "://" .
			(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR']) .
			$_SERVER["REQUEST_URI"];
}

/**
 * Uses PHP's filter_var() to validate e-mail addresses, and also ensures the e-mail address
 * is shorter than 255 characters (limit in our database for e-mail addresses).
 *
 * TODO support UTF-8 email addresses.
 */
function is_valid_email($e) {
	return strlen($e) <= 255 && filter_var($e, FILTER_VALIDATE_EMAIL);
}

function is_valid_url($e) {
	$e = strtolower($e);
	return strlen($e) <= 255 &&
		(substr($e, 0, strlen("http://")) == "http://" || substr($e, 0, strlen("https://")) == "https://");
}

/**
 * Generate a random key of the specified length. This key needs to be
 * alphanumeric. Case-sensitivity is not specified.
 */
function generate_key($length = 32) {
	$new_password = "";
	for ($i = 0; $i < $length; $i++) {
		$new_password .= sprintf("%01x", rand(0,0xf));
	}
	return $new_password;
}

function get_openid_host() { return get_site_config('openid_host'); }

// from http://php.net/manual/en/function.stats-standard-deviation.php
function stdev($aValues, $bSample = false) {
    $fMean = array_sum($aValues) / count($aValues);
    $fVariance = 0.0;
    foreach ($aValues as $i) {
        $fVariance += pow($i - $fMean, 2);
    }
    $fVariance /= ( $bSample ? count($aValues) - 1 : count($aValues) );
    return (float) sqrt($fVariance);
}

function number_format_precision($n, $precision) {
	// if we have 100.x, we only want $precision = 6
	if ($n > 1) {
		$precision -= (log($n) / log(10) - 1);
	}

	return number_format_autoprecision($n, $precision);
}

function number_format_autoprecision($n, $precision = 8, $dec_point = ".", $thousands_sep = ",") {
	// find the lowest precision that we need
	for ($i = 0; $i < $precision - 1; $i++) {
		if (number_format($n, (int) $i, ".", "") == $n) {
			$precision = (int) $i;
			break;
		}
	}

	return number_format($n, $precision, $dec_point, $thousands_sep);
}

// remove any commas; intended to be reverse of number_format()
function number_unformat($value) {
	return str_replace(",", "", $value);
}

/**
 * @return the error message back
 */
function log_error($error) {
	// TODO send an email, or insert something into the database
	// for now, just echo something
	echo '<div class="error">Error: ' . htmlspecialchars($error) . '</div>';
	return $error;
}

class ServiceException extends Exception { }

class WebException extends Exception { }

class IllegalArgumentException extends Exception { }

function set_temporary_messages($m) {
	if ($m === null) {
		unset($_SESSION["temporary_messages"]);
	} else {
		if (!is_array($m))
			$m = array($m);
		$_SESSION["temporary_messages"] = $m;
	}
}

$global_temporary_messages = isset($_SESSION["temporary_messages"]) ? $_SESSION["temporary_messages"] : null;	// only lasts a single request
set_temporary_messages(null);	// reset
function get_temporary_messages() {
	global $global_temporary_messages;
	return $global_temporary_messages === null ? array() : $global_temporary_messages;
}

function set_temporary_errors($m) {
	if ($m === null) {
		unset($_SESSION["temporary_errors"]);
	} else {
		if (!is_array($m))
			$m = array($m);
		$_SESSION["temporary_errors"] = $m;
	}
}

$global_temporary_errors = isset($_SESSION["temporary_errors"]) ? $_SESSION["temporary_errors"] : null;	// only lasts a single request
set_temporary_errors(null);	// reset
function get_temporary_errors() {
	global $global_temporary_errors;
	return $global_temporary_errors === null ? array() : $global_temporary_errors;
}

class EscapedException extends Exception { }

