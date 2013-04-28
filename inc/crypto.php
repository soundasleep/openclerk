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
		default:	return "Unknown (" . htmlspecialchars($n) . ")";
	}
}

function get_blockchain_currencies() {
	return array(
		"Blockchain" => array('btc'),
		"Litecoin Explorer" => array('ltc'),
	);
}

function get_exchange_name($n) {
	switch ($n) {
		case "bitnz": 	return "BitNZ";
		case "btce": 	return "BTC-E";
		case "mtgox": 	return "Mt.Gox";
		case "litecoinglobal": return "Litecoin Global";
		default:		return "Unknown (" . htmlspecialchars($n) . ")";
	}
}

function get_exchange_pairs() {
	return array(
		"bitnz" => array(array('nzd', 'btc')),
		"btce" => array(array('btc', 'ltc'), array('usd', 'btc'), array('usd', 'ltc'), array('btc', 'nmc')),
		"mtgox" => array(array('usd', 'btc')),
	);
}

function get_security_exchange_pairs() {
	return array(
		"litecoinglobal" => array('ltc'),
	);
}

function get_supported_wallets() {
	return array(
		"Generic API" => get_all_currencies(),
		"BTC-E" => array('btc', 'ltc', 'nmc', 'usd'),
		"Pool-x.eu" => array('ltc'),
		"Mt.Gox" => array('btc', 'usd'),
		"Litecoin Global" => array('ltc'),
	);
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
		'summary_btc' => array('currency' => 'btc', 'key' => 'btc', 'title' => get_currency_name('btc'), 'short_title' => 'BTC'),
		'summary_ltc' => array('currency' => 'ltc', 'key' => 'ltc', 'title' => get_currency_name('ltc'), 'short_title' => 'LTC'),
		'summary_nmc' => array('currency' => 'nmc', 'key' => 'nmc', 'title' => get_currency_name('nmc'), 'short_title' => 'NMC'),
		'summary_usd_btce' => array('currency' => 'usd', 'key' => 'usd_btce', 'title' => get_currency_name('usd') . " (converted through BTC-E)", 'short_title' => 'USD (BTC-E)'),
		'summary_usd_mtgox' => array('currency' => 'usd', 'key' => 'usd_mtgox', 'title' => get_currency_name('usd') . " (converted through Mt.Gox)", 'short_title' => 'USD (Mt.Gox)'),
		'summary_nzd' => array('currency' => 'nzd', 'key' => 'nzd', 'title' => get_currency_name('nzd'), 'short_title' => 'NZD'),
	);
}

/**
 * Total conversions: all currencies to a single currency
 * TODO I don't think these take into account e.g. NZD -> USD
 */
function get_total_conversion_summary_types() {
	return array(
		'nzd' => array('currency' => 'nzd', 'title' => get_currency_name('nzd'), 'short_title' => 'NZD'),
		'usd_btce' => array('currency' => 'nzd', 'title' => get_currency_name('usd') . " (converted through BTC-E)", 'short_title' => 'USD (BTC-E)'),
		'usd_mtgox' => array('currency' => 'nzd', 'title' => get_currency_name('usd') . " (converted through Mt.Gox)", 'short_title' => 'USD (Mt.Gox)'),
	);
}

function account_data_grouped() {
	return array(
		'Addresses' => array(
			'blockchain' => array('url' => 'accounts_blockchain', 'title' => 'BTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'btc\''),
			'litecoin' => array('url' => 'accounts_litecoin', 'title' => 'LTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'ltc\''),
		),
		'Mining pools' => array(
			'poolx' => array('url' => 'accounts_poolx', 'title' => 'Pool-X.eu accounts', 'label' => 'account', 'table' => 'accounts_poolx', 'group' => 'accounts'),
		),
		'Exchanges' => array(
			'mtgox' => array('url' => 'accounts_mtgox', 'title' => 'Mt.Gox accounts', 'label' => 'account', 'table' => 'accounts_mtgox', 'group' => 'accounts'),
			'btce' => array('url' => 'accounts_btce', 'title' => 'BTC-E accounts', 'label' => 'account', 'table' => 'accounts_btce', 'group' => 'accounts'),
			'litecoinglobal' => array('url' => 'accounts_litecoinglobal', 'title' => 'Litecoin Global accounts', 'label' => 'account', 'table' => 'accounts_litecoinglobal', 'group' => 'accounts'),
		),
		'Other' => array(
			'generic' => array('url' => 'accounts_generic', 'title' => 'Generic APIs', 'label' => 'API', 'table' => 'accounts_generic', 'group' => 'accounts'),
		),
		'Hidden' => array(
			'graph_pages' => array('label' => 'Graph page', 'table' => 'graph_pages', 'group' => 'graph_pages', 'query' => ' AND is_removed=0'),
			'summaries' => array('label' => 'Currency summaryies', 'table' => 'summaries', 'group' => 'summaries'),
		),
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
	$order = 1;
	$q = db()->prepare("INSERT INTO graphs SET page_id=?,graph_type='btc_equivalent',width=2,height=2,page_order=" . $order++);
	$q->execute(array($page_id));
	$q = db()->prepare("INSERT INTO graphs SET page_id=?,graph_type='mtgox_btc_table',width=1,height=2,page_order=" . $order++);
	$q->execute(array($page_id));
	$q = db()->prepare("INSERT INTO graphs SET page_id=?,graph_type='fiat_converted_table',width=2,height=2,page_order=" . $order++);
	$q->execute(array($page_id));
	$q = db()->prepare("INSERT INTO graphs SET page_id=?,graph_type='balances_offset_table',width=4,height=2,page_order=" . $order++);
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
	if (strlen($address) >= 27 && strlen($address) <= 34 && ((substr($address, 0, 1) == "1" || substr($address, 0, 1) == "3"))
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}

function is_valid_ltc_address($address) {
	// based on is_valid_btc_address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "L")
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

function is_valid_litecoinglobal_apikey($key) {
	// not sure what the format should be, seems to be 64 character hex
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
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

