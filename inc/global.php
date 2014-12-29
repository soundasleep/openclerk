<?php

/*
require(__DIR__ . (disabled) "/locale.php");
require(__DIR__ . (disabled) "/standard.php");
*/
require(__DIR__ . "/../vendor/autoload.php");

require(__DIR__ . "/classes.php");
require(__DIR__ . "/config.php");
define('LIGHTOPENID_TIMEOUT', get_site_config('get_openid_timeout') * 1000);

// set up (db, page) metrics
// (need to do this before performance_metrics_page_start())
Openclerk\MetricsHandler::init(db());

// before loading sessions
require(__DIR__ . "/performance.php");
performance_metrics_page_start();

require(__DIR__ . "/security.php");
require(__DIR__ . "/email.php");
require(__DIR__ . "/crypto.php");
require(__DIR__ . "/premium.php");
require(__DIR__ . "/heavy.php");
require(__DIR__ . "/kb.php");

require(__DIR__ . "/routes.php");

// issue #152: support i18n
require(__DIR__ . "/i18n.php");
function missing_locale_string($key, $locale) {
  log_uncaught_exception(new LocaleException("Locale '$locale': Missing key '$key'"));
}
if (isset($_COOKIE["locale"]) && !isset($_SESSION["locale"]) && in_array($_COOKIE["locale"], get_all_locales())) {
  set_locale($_COOKIE["locale"]);
}

$db_instance = null;

function db() {
  global $db_instance;
  if ($db_instance === null) {
    if (config("database_slave")) {
      $db_instance = new \Db\ReplicatedConnection(
        config("database_host_master"),
        config("database_host_slave"),
        config("database_name"),
        config("database_username"),
        config("database_password"),
        config("database_port"),
        config("database_timezone")
      );
    } else {
      $db_instance = new \Db\SoloConnection(
        config("database_name"),
        config("database_username"),
        config("database_password"),
        config("database_host_master"),
        config("database_port"),
        config("database_timezone")
      );
    }
  }
  return $db_instance;
}

function db_master() {
  return db()->getMaster();
}

function db_slave() {
  return db()->getSlave();
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
  if (function_exists('my_content_type_exception_handler')) {
    my_content_type_exception_handler($e);
  } else {
    echo "Error: " . htmlspecialchars($e->getMessage());
    if (is_localhost()) {
      // only display trace locally
      echo "<br>Trace:";
      print_exception_trace($e);
    }
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
  try {
    $serialized = serialize($e);
  } catch (Exception $e) {
    $serialized = $e->getMessage() . ": " . print_r($e, true);
  }
  $q->execute(array_join(array(
    // clamp messages to 255 characters
    mb_substr($e->getMessage(), 0, 255),
    mb_substr($e->getPrevious() ? $e->getPrevious()->getMessage() : "", 0, 255),
    mb_substr($e->getFile(), 0, 255),
    $e->getLine(),
    mb_substr($serialized, 0, 65535),
  ), $extra_args));
}

class FatalException /* cannot extend Exception, since getMessage() etc are final */ {
  var $wrapped;

  public function __construct($error) {
    $this->wrapped = $error;
  }

  public function getMessage() {
    return $this->wrapped['message'];
  }
  public function getFile() {
    return $this->wrapped['file'];
  }
  public function getCode() {
    return $this->wrapped['type'];
  }
  public function getLine() {
    return $this->wrapped['line'];
  }
  public function getPrevious() {
    return null;
  }
}
register_shutdown_function('fatal_handler');
/**
 * Allows for capturing fatal errors (missing includes, undefined functions etc)
 */
function fatal_handler() {
  $error = error_get_last();
  if ($error['type'] == E_ERROR || $error['type'] == E_CORE_ERROR || $error['type'] == E_COMPILE_ERROR) {
    log_uncaught_exception(new FatalException($error));
  }
}

function redirect($url) {
  if (strpos($url, "\n") !== false) {
    throw new Exception("Invalid multiline URL '$url'");
  }
  header('Location: ' . $url);
  die();
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
  $format = 'Y-m-d H:i:s';  // 2010-01-01 01:01:01, i.e. no timezone data. TODO assumes that the database is in the same timezone as the app
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
      return false; // we found a key in $a that isn't in $b
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
  if ($date == null || $date == 0)
    return "<em>" . t("never") . "</em>";

  if (!is_numeric($date))
    $date = strtotime($date);

  $secs = time() - $date;
  if ($secs == 0) {
    return "<em>" . ht("now") . "</em>";
  } elseif ($secs < 0) {
    if ($future_suffix === false) {
      return t(":time in the future", array(':time' => seconds_to_string(-$secs)));
    } else if ($future_suffix === "") {
      return seconds_to_string(-$secs);
    } else {
      // this form shouldn't be used
      return seconds_to_string(-$secs) . $future_suffix;
    }
  } else {
    if ($suffix === false) {
      return t(":time ago", array(':time' => seconds_to_string($secs)));
    } else if ($future_suffix === "") {
      return seconds_to_string($secs);
    } else {
      // this form shouldn't be used
      return seconds_to_string($secs) . $suffix;
    }
  }
}

function seconds_to_string($secs) {
  if ($secs == 0)
    return "<em>" . ht("now") . "</em>";
  else if ($secs < 60)
    return plural("sec", "sec", ($secs));
  else if ($secs < 60 * 60)
    return plural("min", "min", ($secs / 60));
  else if ($secs < (60 * 60 * 24))
    return plural("hour", "hours", ($secs / (60 * 60)));
  else if ($secs < (60 * 60 * 24 * 31))
    return plural("day", "days", ($secs / (60 * 60 * 24)));
  else if (year_count($secs) < 1)
    return plural("month", "months", (int) ($secs / (60 * 60 * 24 * (365.242/12))));
  else
    return plural("year", "years", (year_count($secs)), 1);
}

function recent_format_html($date, $suffix = false, $future_suffix = false) {
  return '<span title="' . ($date ? htmlspecialchars(iso_date($date)) : ht("Never")) . '">' . recent_format($date, $suffix, $future_suffix) . '</span>';
}

function expected_delay_html($minutes) {
  if ($minutes == 0) {
    return "<i>" . ht("none") . "</i>";
  } else if ($minutes < 60) {
    return "&lt; " . plural("min", ceil($minutes));
  } else if ($minutes < (60 * 60)) {
    return "&lt; " . plural("hour", ceil($minutes / 60));
  } else {
    return "&lt; " . plural("day", ceil($minutes / (60 * 60)));
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
 * Return a string with all " characters encoded.
 * {@code addslashes()} just quotes ALL special characters (including '), which is not suitable
 * for encoding a PHP string.
 */
function phpescapestring($s) {
  return str_replace("\"", "\\\"", $s);
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
    if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] && !defined('FORCE_NO_RELATIVE')) {
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
function old_url_for($module, $arguments = array()) {
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

function link_to($url, $text = false) {
  if ($text === false) {
    return link_to($url, $url);
  }
  return "<a href=\"" . htmlspecialchars($url) . "\">" . htmlspecialchars($text) . "</a>";
}

/**
 * Returns the current request URL along with hostname and $_GET parameters.
 */
function request_url() {
  return ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https" : "http") . "://" .
      (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR']) .
      $_SERVER["REQUEST_URI"];
}

/**
 * Returns the current request path without any hostname or $_GET parameters.
 * Returns the current request URL along with $_GET parameters, relative to
 * {@code get_site_config('absolute_url')}.
 */
function request_url_relative() {
  $url = str_replace("https://", "http://", request_url());
  if (strpos($url, "?") !== false) {
    $url = substr($url, 0, strpos($url, "?"));
  }
  $absolute = str_replace("https://", "http://", get_site_config('absolute_url'));

  $result = str_replace($absolute, "", $url);
  if (!$result) {
    $result = "index";
  }
  return $result;
}

/**
 * Uses PHP's filter_var() to validate e-mail addresses, and also ensures the e-mail address
 * is shorter than 255 characters (limit in our database for e-mail addresses).
 *
 * NOTE this requires MySQL 5+ which uses VARCHAR(32) to define 32 characters, not 32 bytes,
 * therefore we can use mb_strlen().
 *
 * To support UTF-8 email addresses, we aren't too picky about edge cases; if it looks like
 * an e-mail address, accept it.
 */
function is_valid_email($e) {
  return mb_strlen($e) <= 255 && preg_match("/^[^@ ]+@([^@ ]+\\.)+[^@ ]+$/u", $e);
}

function is_valid_url($e) {
  $e = mb_strtolower($e);
  return mb_strlen($e) <= 255 &&
    (mb_substr($e, 0, mb_strlen("http://")) == "http://" || mb_substr($e, 0, mb_strlen("https://")) == "https://");
}

/**
 * Is this user a 'new user' w.r.t. {@code new_user_premium_update_hours}?
 */
function user_is_new($user) {
  return get_site_config('new_user_premium_update_hours') && strtotime($user['created_at']) > strtotime('-' . get_site_config('new_user_premium_update_hours') . ' hour');
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

/**
 * Format a number to the lowest precision that's necessary, to a maximum of the
 * given precision.
 */
function number_format_autoprecision($n, $precision = 8, $dec_point = ".", $thousands_sep = ",") {
  if (!is_numeric($n) && $n /* anything falsey is okay to be numeric */ && is_localhost()) {
    throw new Exception("'$n' is not numeric");
  }

  // find the lowest precision that we need
  for ($i = 0; $i < $precision - 1; $i++) {
    if (number_format($n, (int) $i, ".", "") == $n) {
      $precision = (int) $i;
      break;
    }
  }

  return number_format($n, $precision, $dec_point, $thousands_sep);
}

/**
 * Format a number to a human readable amount of precision.
 */
function number_format_human($n, $extra_precision = 0) {
  if (abs($n) < 1e-4) {
    return number_format_autoprecision($n, 8 + $extra_precision, '.', '');
  } else if (abs($n) < 1e-2) {
    return number_format_autoprecision($n, 6 + $extra_precision, '.', '');
  } else if (abs($n) < 1e4) {
    return number_format_autoprecision($n, 4 + $extra_precision, '.', '');
  } else if (abs($n) < 1e6) {
    return number_format_autoprecision($n, 2 + $extra_precision, '.', '');
  } else {
    return number_format_autoprecision($n, 0 + $extra_precision, '.', '');
  }
}

// remove any commas; intended to be reverse of number_format()
function number_unformat($value) {
  return str_replace(",", "", $value);
}

/**
 * Tag the current page as one that can be cached by the client;
 * sets Expires, Cache-Control etc headers.
 *
 * <p>Doesn't do anything with 304 Not Modified.
 *
 * <p>Uses {@code default_cache_seconds} seconds as a default cache period.
 */
function allow_cache($seconds = false) {
  if ($seconds === false) {
    $seconds = get_site_config('default_cache_seconds');
  }

  $gmdate = 'D, d M Y H:i:s';
  header('Cache-Control: private');   // may only be cached in private cache.
  header('Pragma: private');
  header('Last-Modified: ' . gmdate($gmdate, time()) . ' GMT');
  header('Expires: ' . gmdate($gmdate, time() + $seconds) . ' GMT');
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

function is_localhost() {
  return $_SERVER['SERVER_NAME'] === "localhost" ||
    $_SERVER['SERVER_NAME'] === "localhost.openclerk.org";
}

function set_temporary_messages($m) {
  if (defined('NO_SESSION')) {
    if ($m === null) {
      // does nothing
      return false;
    }
    throw new Exception("Cannot set temporary messages with no session");
  }
  if ($m === null) {
    unset($_SESSION["temporary_messages"]);
  } else {
    if (!is_array($m))
      $m = array($m);
    $_SESSION["temporary_messages"] = $m;
  }
}

$global_temporary_messages = isset($_SESSION["temporary_messages"]) ? $_SESSION["temporary_messages"] : null; // only lasts a single request
set_temporary_messages(null); // reset
function get_temporary_messages() {
  global $global_temporary_messages;
  return $global_temporary_messages === null ? array() : $global_temporary_messages;
}

function set_temporary_errors($m) {
  if (defined('NO_SESSION')) {
    if ($m === null) {
      // does nothing
      return false;
    }
    throw new Exception("Cannot set temporary errors with no session");
  }
  if ($m === null) {
    unset($_SESSION["temporary_errors"]);
  } else {
    if (!is_array($m))
      $m = array($m);
    $_SESSION["temporary_errors"] = $m;
  }
}

$global_temporary_errors = isset($_SESSION["temporary_errors"]) ? $_SESSION["temporary_errors"] : null; // only lasts a single request
set_temporary_errors(null); // reset
function get_temporary_errors() {
  global $global_temporary_errors;
  return $global_temporary_errors === null ? array() : $global_temporary_errors;
}

class EscapedException extends Exception { }

function safe_include_arg($arg) {
  // take out any relative paths etc
  return preg_replace("/[^a-z0-9_\-]/i", "", $arg);
}
