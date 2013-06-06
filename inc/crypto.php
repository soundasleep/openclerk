<?php

/**
 * Defines all of the interesting properties of the web appliation:
 * what currencies are supported, what pairs, etc.
 */

function get_all_currencies() {
	return array("btc", "ltc", "nmc", "ftc", "usd", "eur", "nzd");
}

function get_currency_name($n) {
	switch ($n) {
		case "btc":	return "Bitcoin";
		case "ltc":	return "Litecoin";
		case "ftc": return "Feathercoin";
		case "nmc":	return "Namecoin";
		case "usd":	return "United States dollar";
		case "nzd":	return "New Zealand dollar";
		case "eur": return "Euro";
		default:	return "Unknown (" . htmlspecialchars($n) . ")";
	}
}

function get_blockchain_currencies() {
	return array(
		"Blockchain" => array('btc'),
		"Litecoin Explorer" => array('ltc'),
		"Feathercoin Search" => array('ftc'),
	);
}

function get_exchange_name($n) {
	switch ($n) {
		case "bitnz": 		return "BitNZ";
		case "btce": 		return "BTC-e";
		case "mtgox": 		return "Mt.Gox";
		case "bips":		return "BIPS";
		case "litecoinglobal": return "Litecoin Global";
		case "btct": 		return "BTC Trading Co.";
		case "cryptostocks": return "Cryptostocks";
		case "generic":		return "Generic API";
		case "offsets":		return "Offsets";
		case "blockchain": 	return "Blockchain";	// generic
		case "poolx":		return "Pool-x.eu";
		case "wemineltc": 	return "WeMineLTC";
		case "givemeltc": 	return "Give Me LTC";
		case "vircurex": 	return "Vircurex";
		case "slush":		return "Slush's pool";
		case "btcguild": 	return "BTC Guild";
		case "50btc": 		return "50BTC";
		case "hypernova":	return "Hypernova";
		default:			return "Unknown (" . htmlspecialchars($n) . ")";
	}
}

function get_exchange_pairs() {
	return array(
		"bitnz" => array(array('nzd', 'btc')),
		"btce" => array(array('btc', 'ltc'), array('usd', 'btc'), array('usd', 'ltc'), array('btc', 'nmc'), array('btc', 'ftc'), array('eur', 'btc')),
		"mtgox" => array(array('usd', 'btc'), array('eur', 'btc')),
		"vircurex" => array(array('usd', 'btc'), array('btc', 'ltc'), array('usd', 'ltc'), array('btc', 'nmc'), array('usd', 'nmc'), array('ltc', 'nmc'), array('eur', 'btc')),
	);
}

function get_security_exchange_pairs() {
	return array(
		"litecoinglobal" => array('ltc'),
		"btct" => array('btc'),
		"cryptostocks" => array('btc', 'ltc'),
	);
}

function get_supported_wallets() {
	return array(
		// alphabetically sorted, except for generic
		"50btc" => array('btc'),
		"bips" => array('btc', 'usd'),
		"btce" => array('btc', 'ltc', 'nmc', 'usd', 'ftc', 'eur'),
		"btcguild" => array('btc', 'nmc'),
		"btct" => array('btc'),
		"cryptostocks" => array('btc', 'ltc'),
		"givemeltc" => array('ltc'),
		"hypernova" => array('ltc'),
		"litecoinglobal" => array('ltc'),
		"mtgox" => array('btc', 'usd', 'eur'),
		"poolx" => array('ltc'),
		"slush" => array('btc', 'nmc'),
		"vircurex" => array('btc', 'ltc', 'nmc', 'usd', 'eur'),
		"wemineltc" => array('ltc'),
		"generic" => get_all_currencies(),
	);
}

function get_new_supported_wallets() {
	return array("hypernova");
}

function crypto_address($currency, $address) {
	switch ($currency) {
		case 'btc': return btc_address($address);
		case 'ltc': return ltc_address($address);
		case 'ftc': return ftc_address($address);
		default: return htmlspecialchars($address);
	}
}

function get_summary_types() {
	return array(
		'summary_btc' => array('currency' => 'btc', 'key' => 'btc', 'title' => get_currency_name('btc'), 'short_title' => 'BTC'),
		'summary_ltc' => array('currency' => 'ltc', 'key' => 'ltc', 'title' => get_currency_name('ltc'), 'short_title' => 'LTC'),
		'summary_nmc' => array('currency' => 'nmc', 'key' => 'nmc', 'title' => get_currency_name('nmc'), 'short_title' => 'NMC'),
		'summary_ftc' => array('currency' => 'ftc', 'key' => 'ftc', 'title' => get_currency_name('ftc'), 'short_title' => 'FTC'),
		'summary_usd_btce' => array('currency' => 'usd', 'key' => 'usd_btce', 'title' => get_currency_name('usd') . " (converted through BTC-e)", 'short_title' => 'USD (BTC-E)'),
		'summary_usd_mtgox' => array('currency' => 'usd', 'key' => 'usd_mtgox', 'title' => get_currency_name('usd') . " (converted through Mt.Gox)", 'short_title' => 'USD (Mt.Gox)'),
		'summary_usd_vircurex' => array('currency' => 'usd', 'key' => 'usd_vircurex', 'title' => get_currency_name('usd') . " (converted through Vircurex)", 'short_title' => 'USD (Vircurex)'),
		'summary_nzd' => array('currency' => 'nzd', 'key' => 'nzd', 'title' => get_currency_name('nzd'), 'short_title' => 'NZD'),
		'summary_eur_btce' => array('currency' => 'eur', 'key' => 'eur_btce', 'title' => get_currency_name('eur') . " (converted through BTC-e)", 'short_title' => 'EUR (BTC-E)'),
		'summary_eur_mtgox' => array('currency' => 'eur', 'key' => 'eur_mtgox', 'title' => get_currency_name('eur') . " (converted through Mt.Gox)", 'short_title' => 'EUR (Mt.Gox)'),
		'summary_eur_vircurex' => array('currency' => 'eur', 'key' => 'eur_vircurex', 'title' => get_currency_name('eur') . " (converted through Vircurex)", 'short_title' => 'EUR (Vircurex)'),
	);
}

/**
 * Total conversions: all currencies to a single currency, where possible.
 * (e.g. there's no exchange defined yet that converts NZD -> USD)
 */
function get_total_conversion_summary_types() {
	return array(
		'nzd' => array('currency' => 'nzd', 'title' => get_currency_name('nzd'), 'short_title' => 'NZD'),
		'usd_btce' => array('currency' => 'usd', 'title' => get_currency_name('usd') . " (converted through BTC-e)", 'short_title' => 'USD (BTC-E)'),
		'usd_mtgox' => array('currency' => 'usd', 'title' => get_currency_name('usd') . " (converted through Mt.Gox)", 'short_title' => 'USD (Mt.Gox)'),
		'usd_vircurex' => array('currency' => 'usd', 'title' => get_currency_name('usd') . " (converted through Vircurex)", 'short_title' => 'USD (Vircurex)'),
		'eur_btce' => array('currency' => 'eur', 'title' => get_currency_name('eur') . " (converted through BTC-e)", 'short_title' => 'EUR (BTC-E)'),
		'eur_mtgox' => array('currency' => 'eur', 'title' => get_currency_name('eur') . " (converted through Mt.Gox)", 'short_title' => 'EUR (Mt.Gox)'),
		'eur_vircurex' => array('currency' => 'eur', 'title' => get_currency_name('eur') . " (converted through Vircurex)", 'short_title' => 'EUR (Vircurex)'),
	);
}

/**
 * Crypto conversions: all cryptocurrencies to a single currency.
 */
function get_crypto_conversion_summary_types() {
	return array(
		'btc' => array('currency' => 'btc', 'title' => get_currency_name('btc'), 'short_title' => 'BTC'),
		'ltc' => array('currency' => 'ltc', 'title' => get_currency_name('ltc'), 'short_title' => 'LTC'),
		'nmc' => array('currency' => 'nmc', 'title' => get_currency_name('nmc'), 'short_title' => 'NMC'),
		'ftc' => array('currency' => 'ftc', 'title' => get_currency_name('ftc'), 'short_title' => 'FTC'),
	);
}

function account_data_grouped() {
	$data = array(
		'Addresses' => array(
			'blockchain' => array('url' => 'accounts_blockchain', 'title' => 'BTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'btc\''),
			'litecoin' => array('url' => 'accounts_litecoin', 'title' => 'LTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'ltc\''),
			'feathercoin' => array('url' => 'accounts_feathercoin', 'title' => 'FTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'ftc\''),
		),
		'Mining pools' => array(
			'poolx' => array('url' => 'accounts_poolx', 'title' => 'Pool-X.eu accounts', 'label' => 'account', 'table' => 'accounts_poolx', 'group' => 'accounts'),
			'slush' => array('url' => 'accounts_slush', 'title' => 'Slush\'s pool accounts', 'label' => 'account', 'table' => 'accounts_slush', 'group' => 'accounts'),
			'wemineltc' => array('url' => 'accounts_wemineltc', 'title' => 'WeMineLTC accounts', 'label' => 'account', 'table' => 'accounts_wemineltc', 'group' => 'accounts'),
			'givemeltc' => array('url' => 'accounts_givemeltc', 'title' => 'Give Me LTC accounts', 'label' => 'account', 'table' => 'accounts_givemeltc', 'group' => 'accounts'),
			'btcguild' => array('url' => 'accounts_btcguild', 'title' => 'BTC Guild accounts', 'label' => 'account', 'table' => 'accounts_btcguild', 'group' => 'accounts'),
			'50btc' => array('url' => 'accounts_50btc', 'title' => '50BTC accounts', 'label' => 'account', 'table' => 'accounts_50btc', 'group' => 'accounts'),
			'hypernova' => array('url' => 'accounts_hypernova', 'title' => 'Hypernova accounts', 'label' => 'account', 'table' => 'accounts_hypernova', 'group' => 'accounts'),
		),
		'Exchanges' => array(
			'mtgox' => array('url' => 'accounts_mtgox', 'label' => 'account', 'table' => 'accounts_mtgox', 'group' => 'accounts'),
			'btce' => array('url' => 'accounts_btce', 'label' => 'account', 'table' => 'accounts_btce', 'group' => 'accounts'),
			'litecoinglobal' => array('url' => 'accounts_litecoinglobal', 'label' => 'account', 'table' => 'accounts_litecoinglobal', 'group' => 'accounts'),
			'btct' => array('url' => 'accounts_btct', 'label' => 'account', 'table' => 'accounts_btct', 'group' => 'accounts'),
			'vircurex' => array('url' => 'accounts_vircurex', 'label' => 'account', 'table' => 'accounts_vircurex', 'group' => 'accounts'),
			'cryptostocks' => array('url' => 'accounts_cryptostocks', 'label' => 'account', 'table' => 'accounts_cryptostocks', 'group' => 'accounts'),
			'bips' => array('url' => 'accounts_bips', 'label' => 'account', 'table' => 'accounts_bips', 'group' => 'accounts'),
		),
		'Other' => array(
			'generic' => array('url' => 'accounts_generic', 'title' => 'Generic APIs', 'label' => 'API', 'table' => 'accounts_generic', 'group' => 'accounts'),
		),
		'Hidden' => array(
			'graph_pages' => array('label' => 'Graph page', 'table' => 'graph_pages', 'group' => 'graph_pages', 'query' => ' AND is_removed=0'),
			'summaries' => array('label' => 'Currency summaryies', 'table' => 'summaries', 'group' => 'summaries'),
		),
	);
	foreach ($data['Exchanges'] as $key => $row) {
		$data['Exchanges'][$key]['title'] = get_exchange_name($key) . " " . $row['label'] . "s";
	}
	return $data;
}

function get_default_openid_providers() {
	return array(
		'google' => array('Google Accounts', 'https://www.google.com/accounts/o8/id'),
		'stackexchange' => array('StackExchange', 'https://openid.stackexchange.com'),
		'yahoo' => array('Yahoo', 'https://me.yahoo.com'),
		'blogspot' => array('Blogspot', 'https://www.blogspot.com/'),
	);
}

function get_permitted_days() {
	$permitted_days = array(
		'45' => array('title' => '45 days', 'days' => 45),
		'90' => array('title' => '90 days', 'days' => 90),
		'180' => array('title' => '180 days', 'days' => 180),
		'year' => array('title' => '1 year', 'days' => 366),
	);
	return $permitted_days;
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
	$q = db()->prepare("INSERT INTO graphs SET page_id=?,graph_type='ticker_matrix',width=2,height=2,page_order=" . $order++);
	$q->execute(array($page_id));
	$q = db()->prepare("INSERT INTO graphs SET page_id=?,graph_type='total_converted_table',width=2,height=2,page_order=" . $order++);
	$q->execute(array($page_id));
	$q = db()->prepare("INSERT INTO graphs SET page_id=?,graph_type='balances_offset_table',width=4,height=2,page_order=" . $order++);
	$q->execute(array($page_id));
	$q = db()->prepare("INSERT INTO graphs SET page_id=?,graph_type='mtgox_usdbtc_daily',width=4,height=2,page_order=" . $order++);
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

function is_valid_ftc_address($address) {
	// based on is_valid_ftc_address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "6")
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}

function is_valid_mmcfe_apikey($key) {
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

function is_valid_btct_apikey($key) {
	// not sure what the format should be, seems to be 64 character hex
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_vircurex_apiusername($key) {
	// this could probably be in any format but should be at least one character
	return strlen($key) >= 1 && strlen($key) <= 255;
}

function is_valid_vircurex_apisecret($key) {
	// this could probably be in any format but should be at least one character
	return strlen($key) >= 1 && strlen($key) <= 255;
}

function is_valid_slush_apitoken($key) {
	// not sure what the format is, but it looks to be [user-id]-[random 32 hex characters]
	return preg_match("#^[0-9]+-[0-9a-f]{32}$#", $key);
}

function is_valid_bips_apikey($key) {
	// looks like a 32 character hex string
	return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_btcguild_apikey($key) {
	// looks like a 32 character hex string
	return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_50btc_apikey($key) {
	// looks like a 32 character hex string
	return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_hypernova_apikey($key) {
	// looks like a 64 character hex string
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_generic_key($key) {
	// this could probably be in any format but should be at least one character
	return strlen($key) >= 1 && strlen($key) <= 255;
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

