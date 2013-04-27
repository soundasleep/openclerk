<?php

/**
 * Defines all of the interesting properties of the web appliation:
 * what currencies are supported, what pairs, etc.
 */

function get_all_currencies() {
	return array("btc", "ltc", "nmc", "usd", "nzd");
}

function get_currency_name($n) {
	switch ($n) {
		case "btc":	return "Bitcoin";
		case "ltc":	return "Litecoin";
		case "nmc":	return "Namecoin";
		case "usd":	return "United States dollar";
		case "nzd":	return "New Zealand dollar";
		default:	return "Unknown";
	}
}

function get_blockchain_currencies() {
	return array('btc');
}

function crypto_address($currency, $address) {
	switch ($currency) {
		case 'btc': return btc_address($address);
		case 'ltc': return ltc_address($address);
		default: return htmlspecialchars($address);
	}
}

function get_summary_types() {
	return array(
		'summary_btc' => array('title' => get_currency_name('btc'), 'short_title' => 'BTC'),
		'summary_ltc' => array('title' => get_currency_name('ltc'), 'short_title' => 'LTC'),
		'summary_nmc' => array('title' => get_currency_name('nmc'), 'short_title' => 'NMC'),
		'summary_usd_btce' => array('title' => get_currency_name('usd') . " (converted through BTC-E)", 'short_title' => 'USD (BTC-E)'),
		'summary_usd_mtgox' => array('title' => get_currency_name('usd') . " (converted through Mt.Gox)", 'short_title' => 'USD (Mt.Gox)'),
		'summary_nzd' => array('title' => get_currency_name('nzd'), 'short_title' => 'NZD'),
	);
}

/**
 * Reset currencies, graph data etc to their defaults.
 */
function reset_user_settings($user_id) {

	$q = db()->prepare("DELETE FROM summaries WHERE user_id=?");
	$q->execute(array($user_id));
	$q = db()->prepare("DELETE FROM summary_instances WHERE user_id=?");
	$q->execute(array($user_id));

	// default currencies
	$q = db()->prepare("INSERT INTO summaries SET user_id=?,summary_type=?");
	$q->execute(array($user_id, 'summary_btc'));
	$q = db()->prepare("INSERT INTO summaries SET user_id=?,summary_type=?");
	$q->execute(array($user_id, 'summary_usd_mtgox'));

	reset_user_graphs($user_id);

}

function reset_user_graphs($user_id) {

	$q = db()->prepare("DELETE FROM graphs WHERE page_id IN (SELECT id AS page_id FROM graph_pages WHERE user_id=?)");
	$q->execute(array($user_id));

	$q = db()->prepare("DELETE FROM graph_pages WHERE user_id=?");
	$q->execute(array($user_id));

	// default page
	$q = db()->prepare("INSERT INTO graph_pages SET user_id=?,title=?");
	$q->execute(array($user_id, "Summary"));
	$page_id = db()->lastInsertId();

	// default graphs
	$q = db()->prepare("INSERT INTO graphs SET page_id=?,graph_type='btc_equivalent',width=2,height=2,page_order=1");
	$q->execute(array($page_id));
	$q = db()->prepare("INSERT INTO graphs SET page_id=?,graph_type='fiat_converted_table',width=2,height=2,page_order=2");
	$q->execute(array($page_id));
	$q = db()->prepare("INSERT INTO graphs SET page_id=?,graph_type='mtgox_btc_table',width=1,height=2,page_order=1");
	$q->execute(array($page_id));
	$q = db()->prepare("INSERT INTO graphs SET page_id=?,graph_type='balances_offset_table',width=1,height=2,page_order=1");
	$q->execute(array($page_id));

}

/**
 * Just returns an array of ('ltc' => 'LTC', 'btc' => 'BTC', ...)
 */
function dropdown_currency_list() {
	$result = array();
	foreach (get_all_currencies() as $c) {
		$result[$c] = strtoupper($c);
	}
	return $result;
}

function is_valid_btc_address($address) {
	// very simple check according to https://bitcoin.it/wiki/Address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "1" || substr($address, 0, 1) == "3")
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}

function is_valid_poolx_apikey($key) {
	// not sure what the format should be, seems to be 64 character hexadecmial
	return strlen($key) == 64 && preg_match("#^[a-z0-9]+$#", $key);
}

function is_valid_btce_apikey($key) {
	// not sure what the format should be
	return strlen($key) == 44 && preg_match("#^[A-Z0-9\-]+$#", $key);
}

function is_valid_btce_apisecret($key) {
	// not sure what the format should be
	return strlen($key) == 64 && preg_match("#^[a-z0-9]+$#", $key);
}

function is_valid_mtgox_apikey($key) {
	// not sure what the format should be
	return strlen($key) == 36 && preg_match("#^[a-z0-9\-]+$#", $key);
}

function is_valid_mtgox_apisecret($key) {
	// not sure what the format should be, looks to be similar to base64 encoding
	return strlen($key) > 36 && preg_match('#^[A-Za-z0-9/\\+=]+$#', $key);
}

function is_valid_currency($c) {
	return in_array($c, get_all_currencies());
}

function is_valid_generic_url($url) {
	return preg_match("#^https?://.+$#im", $url) && strlen($url) < 255;
}

function is_valid_name($s) {
	return strlen($s) < 64;
}

function is_valid_title($s) {
	return strlen($s) < 64;
}

