<?php

/*
require("inc/locale.php");
require("inc/standard.php");
*/
require("inc/config.php");
require("inc/security.php");
require("inc/email.php");
require("inc/recaptcha.php");
require("inc/crypto.php");

$db_instance = false;
function db() {
	global $db_instance;
	if (!$db_instance) {
		$db_instance = new PDO(get_site_config('database_url'), get_site_config('database_username'), get_site_config('database_password'));
		$db_instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	return $db_instance;
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
		echo "<li>" . htmlspecialchars($e2['file']) . "#" . htmlspecialchars($e2['line']) . ": " . htmlspecialchars($e2['function']) . "(" . htmlspecialchars(isset($e2['args']) ? $e2['args'] : "") . ")</li>\n";
	}
	if ($e->getPrevious()) {
		echo "<li>Caused by:";
		print_exception_trace($e->getPrevious());
		echo "</li>";
	}
	echo "</ul>";
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
	$q = db()->prepare("INSERT INTO uncaught_exceptions SET
		message=?,
		previous_message=?,
		filename=?,
		line_number=?,
		raw=?,
		created_at=NOW() $extra_query");
	$q->execute(array_join(array(
		$e->getMessage(),
		$e->getPrevious() ? $e->getPrevious()->getMessage() : "",
		$e->getFile(),
		$e->getLine(),
		serialize($e),
	), $extra_args));
	die;
}
set_exception_handler('my_exception_handler');

function redirect($url) {
	header('Location: ' . $url);
	die();
}

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

function plural($n, $s, $ss) {
	if ($n == 1) {
		return sprintf("%s %s", $n, $s);
	} else {
		return sprintf("%s %s", $n, $ss);
	}
}

function recent_format_html($date, $suffix = false, $future_suffix = false) {
	return '<span title="' . htmlspecialchars(iso_date($date)) . '">' . recent_format($date, $suffix, $future_suffix) . '</span>';
}

function year_count($sec) {
	return $sec / (60 * 60 * 24 * 365.242);
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
 * Generate the url for a particular module (i.e. script) and particular arguments (i.e. query string elements).
 * TODO Currently just assumes everything is .htaccess'd to the root with no subdirs
 */
function url_for($module, $arguments = array()) {
	$query = array();
	if (count($arguments) > 0) {
		foreach ($arguments as $key => $value) {
			$query[] = urlencode($key) . "=" . urlencode($value);
		}
	}
	return $module . /* ".php" . */ (count($query) ? "?" . implode("&", $query) : "");
}

/**
 * Very basic verification function: It needs to have a dot, and an at sign.
 */
function is_valid_email($e) {
	return strpos($e, ".") !== false && strpos($e, "@") !== false;
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

/**
 * @return the error message back
 */
function log_error($error) {
	// TODO send an email, or insert something into the database
	// for now, just echo something
	echo '<div class="error">Error: ' . htmlspecialchars($error) . '</div>';
	return $error;
}

// TODO implement
// also see json_encode: http://stackoverflow.com/questions/168214/pass-a-php-string-to-a-javascript-variable-and-escape-newlines
function json_escape($s) {
	return htmlspecialchars($s);
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
