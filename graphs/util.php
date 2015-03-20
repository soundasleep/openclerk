<?php

/**
 * Goes through an array (which may also contain other arrays) and find
 * the most latest 'created_at' value.
 */
function find_latest_created_at($a, $prefix = false) {
  if (!is_array($a))
    return false;
  $created_at = false;
  foreach ($a as $k => $v) {
    if (!is_numeric($k) && $k == "created_at") {
      $created_at = max($created_at, strtotime($v));
    } else if (is_array($v)) {
      if (!$prefix || substr($k, 0, strlen($prefix)) == $prefix) {
        $created_at = max($created_at, find_latest_created_at($v));
      }
    }
  }
  return $created_at;
}

// a simple alias
function graph_number_format($n) {
  return number_format_human($n);
}

// cached
$global_all_summary_instances = array();

/**
 * Get all summary_instances for the given user, or the current user (through
 * {@link #user_id()}).
 * Cached per user.
 */
function get_all_summary_instances($user_id = false) {
  if ($user_id === false) {
    $user_id = user_id();
  }

  global $global_all_summary_instances;
  if (!isset($global_all_summary_instances[$user_id])) {
    $global_all_summary_instances[$user_id] = array();
    $q = db()->prepare("SELECT * FROM summary_instances WHERE user_id=? AND is_recent=1");
    $q->execute(array($user_id));
    while ($summary = $q->fetch()) {
      $global_all_summary_instances[$user_id][$summary['summary_type']] = $summary;
    }
  }
  return $global_all_summary_instances[$user_id];
}

$global_all_summaries = array();

/**
 * Get all summaries for the given user, or the current user (through
 * {@link #user_id()}).
 * Cached per user.
 */
function get_all_summaries($user_id = false) {
  if ($user_id === false) {
    $user_id = user_id();
  }

  global $global_all_summaries;
  if (!isset($global_all_summaries[$user_id])) {
    $global_all_summaries[$user_id] = array();
    $q = db()->prepare("SELECT * FROM summaries WHERE user_id=?");
    $q->execute(array($user_id));
    while ($summary = $q->fetch()) {
      $global_all_summaries[$user_id][$summary['summary_type']] = $summary;
    }
  }
  return $global_all_summaries[$user_id];
}

$global_all_offset_instances = array();

/**
 * Get all offsets for the given user, or the current user (through
 * {@link #user_id()}).
 * Cached per user.
 */
function get_all_offset_instances($user_id = false) {
  if ($user_id === false) {
    $user_id = user_id();
  }

  global $global_all_offset_instances;
  if (!isset($global_all_offset_instances[$user_id])) {
    $global_all_offset_instances[$user_id] = array();
    $q = db()->prepare("SELECT currency, SUM(balance) AS balance FROM offsets WHERE user_id=? GROUP BY currency");
    $q->execute(array($user_id));
    while ($offset = $q->fetch()) {
      $global_all_offset_instances[$user_id][$offset['currency']] = $offset;
    }
  }
  return $global_all_offset_instances[$user_id];
}

/**
 * Return a list of summary keys for this user
 * (e.g. 'summary_usd_bitstamp', 'summary_btc', ...)
 */
function get_all_summary_currencies($user_id = false) {
  $summaries = get_all_summaries($user_id);
  $result = array();
  foreach ($summaries as $s) {
    // assumes all summaries start with 'summary_CUR_optional'
    $c = substr($s['summary_type'], strlen("summary_"), 3);
    $result[$c] = $s['summary_type'];
  }
  return $result;
}

/**
 * Like {@link #get_all_summary_currencies()}, but returns
 * a list of currencies rather than summary keys.
 */
function get_all_user_currencies($user_id = false) {
  return array_keys(get_all_summary_currencies($user_id));
}

function get_all_conversion_currencies($user_id = false) {
  $summaries = get_all_summaries($user_id);
  $result = array();
  foreach ($summaries as $s) {
    // assumes all summaries start with 'summary_CUR_optional'
    $c = substr($s['summary_type'], strlen("summary_"), 3);
    $result[$s['summary_type']] = $c;
  }
  return $result;
}

// cached
$global_all_recent_rates = null;
// uses the "best" exchanges as defined in get_default_currency_exchange()
function get_all_recent_rates() {
  global $global_all_recent_rates;
  if ($global_all_recent_rates === null) {
    $global_all_recent_rates = array();
    $query = "";
    foreach (get_all_currencies() as $cur) {
      if ($cur == 'btc') continue;  // we don't provide a 'btcbtc' rate
      $exchange = get_default_currency_exchange($cur);
      $query .= "(currency1 = 'btc' AND currency2 = '$cur' AND exchange='$exchange') OR";
      $query .= "(currency1 = '$cur' AND currency2 = 'btc' AND exchange='$exchange') OR";
    }
    $q = db()->prepare("SELECT * FROM ticker_recent WHERE 1 AND ($query 0)");
    $q->execute();
    while ($ticker = $q->fetch()) {
      $global_all_recent_rates[$ticker['currency1'] . $ticker['currency2']] = $ticker;
    }
  }
  return $global_all_recent_rates;
}

class GraphException extends Exception { }

/**
 * Scales the given number down if the ?demo parameter is supplied as part of the request.
 * Useful for displaying demo data.
 */
function demo_scale($value) {
  if (require_get("demo", false)) {
    return $value * 0.05;
  }
  return $value;
}

// $arg0 is from historical_arg0
function get_exchange_historical($arg0, $graph_type, $graph) {
  return url_for('historical', array('id' => $arg0['key'] . '_' . $arg0['pair'][0] . $arg0['pair'][1] . '_daily', 'days' => 180));
}

function get_average_exchange_markets($arg0, $graph_type, $graph) {
  return url_for('average#average_' . $arg0['currency1'], array('currency1' => $arg0['currency1'], 'currency2' => $arg0['currency2']));
}

function get_average_exchange_historical($arg0, $graph_type, $graph) {
  return url_for('historical', array('id' => $arg0['key'] . '_' . $arg0['currency1'] . $arg0['currency2'] . '_daily', 'days' => 180));
}

/**
 * Return a list of (id => title) for the given exchange and currency.
 * Could be cached.
 */
function get_security_instances($exchange, $currency) {
  $result = array();
  $args = array();

  switch ($exchange) {
    case "litecoinglobal":
      $q = db()->prepare("SELECT id, name, name as title FROM securities_litecoinglobal ORDER BY name ASC");
      break;

    case "btct":
      $q = db()->prepare("SELECT id, name, name as title FROM securities_btct ORDER BY name ASC");
      break;

    case "cryptostocks":
      $q = db()->prepare("SELECT id, name, name as title FROM securities_cryptostocks WHERE currency=? ORDER BY name ASC");
      $args = array($currency);
      break;

    case "havelock":
      $q = db()->prepare("SELECT id, name, name as title FROM securities_havelock ORDER BY name ASC");
      break;

    case "bitfunder":
      $q = db()->prepare("SELECT id, name, name as title FROM securities_bitfunder ORDER BY name ASC");
      break;

    case "crypto-trade":
      $q = db()->prepare("SELECT id, name, title FROM securities_cryptotrade WHERE currency=? ORDER BY name ASC");
      $args = array($currency);
      break;

    case "796":
      $q = db()->prepare("SELECT id, name, title FROM securities_796 ORDER BY title ASC");
      break;

    case "litecoininvest":
      $q = db()->prepare("SELECT id, name, name as title FROM securities_litecoininvest ORDER BY title ASC");
      break;

    case "btcinve":
      $q = db()->prepare("SELECT id, name, name as title FROM securities_btcinve ORDER BY title ASC");
      break;

    default:
      throw new GraphException("Unknown security exchange '" . htmlspecialchars($exchange) . "' for currency '" . htmlspecialchars($currency) . "'");
  }

  $q->execute($args);
  while ($sec = $q->fetch()) {
    $result[$sec['id']] = $sec; // keep the name and title
  }
  return $result;

}

function get_security_instances_keys($exchange, $currency) {
  $input = get_security_instances($exchange, $currency);
  $result = array();
  foreach ($input as $key => $value) {
    $result[$key] = $value['name'];
  }
  return $result;
}

function get_security_instance_title($graph_id, $name) {
  $bits = explode("_", $graph_id, 3);
  if (count($bits) != 3) {
    return "[Unknown graph_id type]";
  }
  $input = get_security_instances($bits[1], $bits[2]);
  foreach ($input as $key => $value) {
    if ($value['name'] == $name) {
      return $value['title'];
    }
  }
  return $name; // fallback
}

function get_security_instances_historical($graph_type, $graph, $exchange, $currency) {
  return url_for('historical', array('name' => $graph_type['heading_key'], 'days' => 180, 'id' => 'securities_' . $exchange . '_' . $currency));
}

/**
 * Return a list of (id => title).
 * Could be cached.
 */
function get_external_status_titles() {
  $result = array();
  $q = db()->prepare("SELECT * FROM external_status_types");
  $q->execute();
  $titles = get_external_apis_titles();
  while ($type = $q->fetch()) {
    // we want the title, not the key; and only types that have titles
    if (isset($titles[$type['job_type']])) {
      $result[$type['id']] = $titles[$type['job_type']];
    }
  }
  return $result;
}

/**
 * Return a list of (id => job_type).
 * Could be cached.
 */
function get_external_status_types() {
  $result = array();
  $q = db()->prepare("SELECT * FROM external_status_types");
  $q->execute();
  while ($type = $q->fetch()) {
    $result[$type['id']] = $type['job_type'];
  }
  return $result;
}

function get_external_status_historical($graph_type, $graph) {
  if (!isset($graph['arg0'])) {
    return false;   // this is external_historical page
  }

  $g = get_external_status_types();
  return url_for('external_historical', array('type' => $g[$graph['arg0']]));
}
