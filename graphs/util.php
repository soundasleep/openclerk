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
	return number_format($n, 4, '.', '');
}

// cached
$global_all_summary_instances = null;
function get_all_summary_instances() {
	global $global_all_summary_instances;
	if ($global_all_summary_instances === null) {
		$global_all_summary_instances = array();
		$q = db()->prepare("SELECT * FROM summary_instances WHERE user_id=? AND is_recent=1");
		$q->execute(array(user_id()));
		while ($summary = $q->fetch()) {
			$global_all_summary_instances[$summary['summary_type']] = $summary;
		}
	}
	return $global_all_summary_instances;
}

// cached
$global_all_summaries = null;
function get_all_summaries() {
	global $global_all_summaries;
	if ($global_all_summaries === null) {
		$global_all_summaries = array();
		$q = db()->prepare("SELECT * FROM summaries WHERE user_id=?");
		$q->execute(array(user_id()));
		while ($summary = $q->fetch()) {
			$global_all_summaries[$summary['summary_type']] = $summary;
		}
	}
	return $global_all_summaries;
}

// cached
$global_all_offset_instances = null;
function get_all_offset_instances() {
	global $global_all_offset_instances;
	if ($global_all_offset_instances === null) {
		$global_all_offset_instances = array();
		$q = db()->prepare("SELECT * FROM offsets WHERE user_id=? AND is_recent=1");
		$q->execute(array(user_id()));
		while ($offset = $q->fetch()) {
			$global_all_offset_instances[$offset['currency']] = $offset;
		}
	}
	return $global_all_offset_instances;
}

function get_all_summary_currencies() {
	$summaries = get_all_summaries();
	$result = array();
	foreach ($summaries as $s) {
		// assumes all summaries start with 'summary_CUR_optional'
		$c = substr($s['summary_type'], strlen("summary_"), 3);
		$result[$c] = $s['summary_type'];
	}
	return $result;
}

function get_all_conversion_currencies() {
	$summaries = get_all_summaries();
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
// this also makes assumptions about which is the best exchange for each rate
// e.g. btc-e for btc/ltc, mtgox for usd/btc
function get_all_recent_rates() {
	global $global_all_recent_rates;
	if ($global_all_recent_rates === null) {
		$global_all_recent_rates = array();
		$q = db()->prepare("SELECT * FROM ticker WHERE is_recent=1 AND (
			(currency1 = 'btc' AND currency2 = 'ltc' AND exchange='btce') OR
			(currency1 = 'btc' AND currency2 = 'ftc' AND exchange='btce') OR
			(currency1 = 'btc' AND currency2 = 'nmc' AND exchange='btce') OR
			(currency1 = 'nzd' AND currency2 = 'btc' AND exchange='bitnz') OR
			(currency1 = 'usd' AND currency2 = 'btc' AND exchange='mtgox') OR
			0
		)");
		$q->execute(array(user_id()));
		while ($ticker = $q->fetch()) {
			$global_all_recent_rates[$ticker['currency1'] . $ticker['currency2']] = $ticker;
		}
	}
	return $global_all_recent_rates;
}

class GraphException extends Exception { }

/**
 * Return a list of (id => title).
 * Could be cached.
 */
function get_litecoinglobal_securities_ltc() {
	$result = array();
	$q = db()->prepare("SELECT * FROM securities_litecoinglobal ORDER BY name ASC");
	$q->execute();
	while ($sec = $q->fetch()) {
		$result[$sec['id']] = $sec['name'];
	}
	return $result;
}

/**
 * Return a list of (id => title).
 * Could be cached.
 */
function get_btct_securities_btc() {
	$result = array();
	$q = db()->prepare("SELECT * FROM securities_btct ORDER BY name ASC");
	$q->execute();
	while ($sec = $q->fetch()) {
		$result[$sec['id']] = $sec['name'];
	}
	return $result;
}

/**
 * Return a list of (id => title).
 * Could be cached.
 */
function get_cryptostocks_securities($cur) {
	$result = array();
	$q = db()->prepare("SELECT * FROM securities_cryptostocks WHERE currency=? ORDER BY name ASC");
	$q->execute(array($cur));
	while ($sec = $q->fetch()) {
		$result[$sec['id']] = $sec['name'];
	}
	return $result;
}

function get_cryptostocks_securities_btc() {
	return get_cryptostocks_securities('btc');
}
function get_cryptostocks_securities_ltc() {
	return get_cryptostocks_securities('ltc');
}

/**
 * Return a list of (id => job_type).
 * Could be cached.
 */
function get_external_status_types() {
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
