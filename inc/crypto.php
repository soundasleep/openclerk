<?php

/**
 * Defines all of the interesting properties of the web appliation:
 * what currencies are supported, what pairs, etc.
 */

function get_all_currencies() {
	return array("btc", "ltc", "nmc", "ppc", "ftc", "usd", "eur", "cad", "aud", "nzd");
}

function get_all_hashrate_currencies() {
	return array("btc", "ltc", "nmc");
}

function get_new_supported_currencies() {
	return array("ppc", "cad");
}

function get_all_cryptocurrencies() {
	return array("btc", "ltc", "nmc", "ppc", "ftc");
}

// currencies which we can download balances using explorers etc
function get_address_currencies() {
	return array("btc", "ltc", "ppc", "ftc");	// no NMC yet
}

function get_currency_name($n) {
	switch ($n) {
		case "btc":	return "Bitcoin";
		case "ltc":	return "Litecoin";
		case "ppc":	return "PPCoin";
		case "ftc": return "Feathercoin";
		case "nmc":	return "Namecoin";
		case "usd":	return "United States dollar";
		case "nzd":	return "New Zealand dollar";
		case "aud": return "Australian dollar";
		case "cad": return "Canadian dollar";
		case "eur": return "Euro";
		default:	return "Unknown (" . htmlspecialchars($n) . ")";
	}
}

function get_blockchain_currencies() {
	return array(
		"Blockchain" => array('btc'),
		"Litecoin Explorer" => array('ltc'),
		"CryptoCoin Explorer" => array('ftc', 'ppc'),
	);
}

function get_exchange_name($n) {
	switch ($n) {
		case "bitnz": 		return "BitNZ";
		case "btce": 		return "BTC-e";
		case "mtgox": 		return "Mt.Gox";
		case "bips":		return "BIPS";
		case "litecoinglobal": return "Litecoin Global";
		case "litecoinglobal_wallet": return "Litecoin Global (Wallet)";
		case "litecoinglobal_securities": return "Litecoin Global (Securities)";
		case "btct": 		return "BTC Trading Co.";
		case "btct_wallet": 		return "BTC Trading Co. (Wallet)";
		case "btct_securities": 		return "BTC Trading Co. (Securities)";
		case "cryptostocks": return "Cryptostocks";
		case "cryptostocks_wallet": return "Cryptostocks (Wallet)";
		case "cryptostocks_securities": return "Cryptostocks (Securities)";
		case "generic":		return "Generic API";
		case "offsets":		return "Offsets";		// generic
		case "blockchain": 	return "Blockchain";	// generic
		case "poolx":		return "Pool-x.eu";
		case "wemineltc": 	return "WeMineLTC";
		case "givemecoins":	return "Give Me Coins";
		case "vircurex": 	return "Vircurex";
		case "slush":		return "Slush's pool";
		case "btcguild": 	return "BTC Guild";
		case "50btc": 		return "50BTC";
		case "hypernova":	return "Hypernova";
		case "ltcmineru":	return "LTCMine.ru";
		case "miningforeman": return "Mining Foreman";	// LTC default
		case "miningforeman_ftc": return "Mining Foreman";
		case "havelock":	return "Havelock Investments";
		case "havelock_wallet":	return "Havelock Investments (Wallet)";
		case "havelock_securities":	return "Havelock Investments (Securities)";
		case "bitminter":	return "BitMinter";
		case "liteguardian": return "LiteGuardian";
		case "themoneyconverter": return "TheMoneyConverter";
		case "virtex":		return "VirtEx";
		case "bitstamp":	return "Bitstamp";
		default:			return "Unknown (" . htmlspecialchars($n) . ")";
	}
}

function get_new_exchanges() {
	return array("themoneyconverter", "virtex", "bitstamp");
}

function get_exchange_pairs() {
	return array(
		"bitnz" => array(array('nzd', 'btc')),
		"btce" => array(array('btc', 'ltc'), array('usd', 'btc'), array('usd', 'ltc'), array('btc', 'nmc'), array('btc', 'ppc'), array('btc', 'ftc'), array('eur', 'btc'), array('usd', 'eur'), array('usd', 'nmc')),
		"mtgox" => array(array('usd', 'btc'), array('eur', 'btc'), array('aud', 'btc'), array('cad', 'btc')),
		"vircurex" => array(array('usd', 'btc'), array('btc', 'ltc'), array('usd', 'ltc'), array('btc', 'nmc'), array('btc', 'ppc'), array('btc', 'ftc'), array('usd', 'nmc'), array('ltc', 'nmc'), array('eur', 'btc')),
		"themoneyconverter" => array(array('usd', 'eur'), array('usd', 'aud'), array('usd', 'nzd'), array('usd', 'cad')),
		"virtex" => array(array('cad', 'btc')),
		"bitstamp" => array(array('usd', 'btc')),
	);
}

function get_new_exchange_pairs() {
	return array(
		"btce_usdeur",
		"btce_usdnmc",
		"mtgox_cadbtc",
		"themoneyconverter_usdeur",
		"themoneyconverter_usdaud",
		"themoneyconverter_usdnzd",
		"themoneyconverter_usdcad",
		"virtex_cadbtc",
		"bitstamp_usdbtc",
	);
}

function get_security_exchange_pairs() {
	return array(
		"litecoinglobal" => array('ltc'),
		"btct" => array('btc'),
		"cryptostocks" => array('btc', 'ltc'),
		"havelock" => array('btc'),
	);
}

function get_new_security_exchanges() {
	return array("havelock");
}

function get_supported_wallets() {
	return array(
		// alphabetically sorted, except for generic
		"50btc" => array('btc', 'hash'),
		"bips" => array('btc', 'usd'),
		"bitminter" => array('btc', 'nmc', 'hash'),
		"btce" => array('btc', 'ltc', 'nmc', 'usd', 'ftc', 'eur', 'ppc'),		// used in jobs/btce.php
		"btcguild" => array('btc', 'nmc', 'hash'),
		"btct" => array('btc'),
		"cryptostocks" => array('btc', 'ltc'),
		"givemecoins" => array('ltc', 'btc', 'ftc', 'hash'),
		"havelock" => array('btc'),
		"hypernova" => array('ltc', 'hash'),
		"litecoinglobal" => array('ltc'),
		"liteguardian" => array('ltc'),
		"ltcmineru" => array('ltc'),
		"mtgox" => array('btc', 'usd', 'eur', 'aud', 'cad'),
		"miningforeman" => array('ltc', 'ftc'),
		"poolx" => array('ltc', 'hash'),
		"slush" => array('btc', 'nmc', 'hash'),
		"vircurex" => array('btc', 'ltc', 'nmc', 'ftc', 'usd', 'eur', 'ppc'),		// used in jobs/vircurex.php
		"wemineltc" => array('ltc', 'hash'),
		"generic" => get_all_currencies(),
	);
}

function get_new_supported_wallets() {
	return array("givemecoins");
}

function crypto_address($currency, $address) {
	switch ($currency) {
		case 'btc': return btc_address($address);
		case 'ltc': return ltc_address($address);
		case 'ftc': return ftc_address($address);
		case 'ppc': return ppc_address($address);
		default: return htmlspecialchars($address);
	}
}

function get_summary_types() {
	return array(
		'summary_btc' => array('currency' => 'btc', 'key' => 'btc', 'title' => get_currency_name('btc'), 'short_title' => 'BTC'),
		'summary_ltc' => array('currency' => 'ltc', 'key' => 'ltc', 'title' => get_currency_name('ltc'), 'short_title' => 'LTC'),
		'summary_nmc' => array('currency' => 'nmc', 'key' => 'nmc', 'title' => get_currency_name('nmc'), 'short_title' => 'NMC'),
		'summary_ftc' => array('currency' => 'ftc', 'key' => 'ftc', 'title' => get_currency_name('ftc'), 'short_title' => 'FTC'),
		'summary_ppc' => array('currency' => 'ppc', 'key' => 'ppc', 'title' => get_currency_name('ppc'), 'short_title' => 'PPC'),
		'summary_usd_btce' => array('currency' => 'usd', 'key' => 'usd_btce', 'title' => get_currency_name('usd') . " (converted through BTC-e)", 'short_title' => 'USD (BTC-E)', 'exchange' => 'btce'),
		'summary_usd_mtgox' => array('currency' => 'usd', 'key' => 'usd_mtgox', 'title' => get_currency_name('usd') . " (converted through Mt.Gox)", 'short_title' => 'USD (Mt.Gox)', 'exchange' => 'mtgox'),
		'summary_usd_vircurex' => array('currency' => 'usd', 'key' => 'usd_vircurex', 'title' => get_currency_name('usd') . " (converted through Vircurex)", 'short_title' => 'USD (Vircurex)', 'exchange' => 'virtex'),
		'summary_usd_bitstamp' => array('currency' => 'usd', 'key' => 'usd_bitstamp', 'title' => get_currency_name('usd') . " (converted through Bitstamp)", 'short_title' => 'USD (Bitstamp)', 'exchange' => 'bitstamp'),
		'summary_nzd' => array('currency' => 'nzd', 'key' => 'nzd', 'title' => get_currency_name('nzd'), 'short_title' => 'NZD', 'exchange' => 'bitnz'),
		'summary_eur_btce' => array('currency' => 'eur', 'key' => 'eur_btce', 'title' => get_currency_name('eur') . " (converted through BTC-e)", 'short_title' => 'EUR (BTC-E)', 'exchange' => 'btce'),
		'summary_eur_mtgox' => array('currency' => 'eur', 'key' => 'eur_mtgox', 'title' => get_currency_name('eur') . " (converted through Mt.Gox)", 'short_title' => 'EUR (Mt.Gox)', 'exchange' => 'mtgox'),
		'summary_eur_vircurex' => array('currency' => 'eur', 'key' => 'eur_vircurex', 'title' => get_currency_name('eur') . " (converted through Vircurex)", 'short_title' => 'EUR (Vircurex)', 'exchange' => 'vircurex'),
		'summary_aud_mtgox' => array('currency' => 'aud', 'key' => 'aud_mtgox', 'title' => get_currency_name('aud') . " (converted through Mt.Gox)", 'short_title' => 'AUD (Mt.Gox)', 'exchange' => 'mtgox'),
		'summary_cad_mtgox' => array('currency' => 'usd', 'key' => 'cad_mtgox', 'title' => get_currency_name('cad') . " (converted through Mt.Gox)", 'short_title' => 'CAD (Mt.Gox)', 'exchange' => 'mtgox'),
		'summary_cad_virtex' => array('currency' => 'usd', 'key' => 'cad_virtex', 'title' => get_currency_name('cad') . " (converted through VirtEx)", 'short_title' => 'CAD (VirtEx)', 'exchange' => 'virtex'),
	);
}

function get_default_currency_exchange($c) {
	switch ($c) {
		case "usd": return "mtgox";
		case "nzd": return "bitnz";
		case "eur": return "btce";
		case "aud": return "mtgox";
		case "cad": return "virtex";
		default: throw new Exception("Unknown currency to exchange into: $c");
	}
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
		'usd_bitstamp' => array('currency' => 'usd', 'title' => get_currency_name('usd') . " (converted through Bitstamp)", 'short_title' => 'USD (Bitstamp)'),
		'eur_btce' => array('currency' => 'eur', 'title' => get_currency_name('eur') . " (converted through BTC-e)", 'short_title' => 'EUR (BTC-E)'),
		'eur_mtgox' => array('currency' => 'eur', 'title' => get_currency_name('eur') . " (converted through Mt.Gox)", 'short_title' => 'EUR (Mt.Gox)'),
		'eur_vircurex' => array('currency' => 'eur', 'title' => get_currency_name('eur') . " (converted through Vircurex)", 'short_title' => 'EUR (Vircurex)'),
		'aud_mtgox' => array('currency' => 'aud', 'title' => get_currency_name('aud') . " (converted through Mt.Gox)", 'short_title' => 'AUD (Mt.Gox)'),
		'cad_mtgox' => array('currency' => 'cad', 'title' => get_currency_name('cad') . " (converted through Mt.Gox)", 'short_title' => 'CAD (Mt.Gox)'),
		'cad_virtex' => array('currency' => 'cad', 'title' => get_currency_name('cad') . " (converted through VirtEx)", 'short_title' => 'CAD (VirtEx)'),
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
		'ppc' => array('currency' => 'ppc', 'title' => get_currency_name('ppc'), 'short_title' => 'PPC'),
	);
}

function account_data_grouped() {
	$data = array(
		'Addresses' => array(
			'blockchain' => array('url' => 'accounts_blockchain', 'title' => 'BTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'btc\'', 'wizard' => 'addresses'),
			'litecoin' => array('url' => 'accounts_litecoin', 'title' => 'LTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'ltc\'', 'wizard' => 'addresses'),
			'feathercoin' => array('url' => 'accounts_feathercoin', 'title' => 'FTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'ftc\'', 'wizard' => 'addresses'),
			'ppcoin' => array('url' => 'accounts_ppcoin', 'title' => 'PPC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'ppc\'', 'wizard' => 'addresses'),
		),
		'Mining pools' => array(
			'poolx' => array('url' => 'accounts_poolx', 'label' => 'account', 'table' => 'accounts_poolx', 'group' => 'accounts', 'wizard' => 'pools'),
			'slush' => array('url' => 'accounts_slush', 'label' => 'account', 'table' => 'accounts_slush', 'group' => 'accounts', 'wizard' => 'pools'),
			'wemineltc' => array('url' => 'accounts_wemineltc', 'label' => 'account', 'table' => 'accounts_wemineltc', 'group' => 'accounts', 'wizard' => 'pools'),
			'givemecoins' => array('url' => 'accounts_givemecoins', 'label' => 'account', 'table' => 'accounts_givemecoins', 'group' => 'accounts', 'wizard' => 'pools'),
			'btcguild' => array('url' => 'accounts_btcguild', 'label' => 'account', 'table' => 'accounts_btcguild', 'group' => 'accounts', 'wizard' => 'pools'),
			'50btc' => array('url' => 'accounts_50btc', 'label' => 'account', 'table' => 'accounts_50btc', 'group' => 'accounts', 'wizard' => 'pools'),
			'hypernova' => array('url' => 'accounts_hypernova', 'label' => 'account', 'table' => 'accounts_hypernova', 'group' => 'accounts', 'wizard' => 'pools'),
			'ltcmineru' => array('url' => 'accounts_ltcmineru', 'label' => 'account', 'table' => 'accounts_ltcmineru', 'group' => 'accounts', 'wizard' => 'pools'),
			'miningforeman' => array('url' => 'accounts_miningforeman', 'label' => 'account', 'table' => 'accounts_miningforeman', 'group' => 'accounts', 'suffix' => ' LTC', 'wizard' => 'pools'),
			'miningforeman_ftc' => array('url' => 'accounts_miningforeman_ftc', 'label' => 'account', 'table' => 'accounts_miningforeman_ftc', 'group' => 'accounts', 'suffix' => ' FTC', 'wizard' => 'pools'),
			'bitminter' => array('url' => 'accounts_bitminter', 'label' => 'account', 'table' => 'accounts_bitminter', 'group' => 'accounts', 'wizard' => 'pools'),
			'liteguardian' => array('url' => 'accounts_liteguardian', 'label' => 'account', 'table' => 'accounts_liteguardian', 'group' => 'accounts', 'wizard' => 'pools'),
		),
		'Exchanges' => array(
			'mtgox' => array('url' => 'accounts_mtgox', 'label' => 'account', 'table' => 'accounts_mtgox', 'group' => 'accounts', 'wizard' => 'exchanges'),
			'btce' => array('url' => 'accounts_btce', 'label' => 'account', 'table' => 'accounts_btce', 'group' => 'accounts', 'wizard' => 'exchanges'),
			'litecoinglobal' => array('url' => 'accounts_litecoinglobal', 'label' => 'account', 'table' => 'accounts_litecoinglobal', 'group' => 'accounts', 'wizard' => 'securities'),
			'btct' => array('url' => 'accounts_btct', 'label' => 'account', 'table' => 'accounts_btct', 'group' => 'accounts', 'wizard' => 'securities'),
			'vircurex' => array('url' => 'accounts_vircurex', 'label' => 'account', 'table' => 'accounts_vircurex', 'group' => 'accounts', 'wizard' => 'exchanges'),
			'cryptostocks' => array('url' => 'accounts_cryptostocks', 'label' => 'account', 'table' => 'accounts_cryptostocks', 'group' => 'accounts', 'wizard' => 'securities'),
			'bips' => array('url' => 'accounts_bips', 'label' => 'account', 'table' => 'accounts_bips', 'group' => 'accounts', 'wizard' => 'exchanges'),
			'havelock' => array('url' => 'accounts_havelock', 'label' => 'account', 'table' => 'accounts_havelock', 'group' => 'accounts', 'wizard' => 'securities'),
		),
		'Other' => array(
			'generic' => array('url' => 'accounts_generic', 'title' => 'Generic APIs', 'label' => 'API', 'table' => 'accounts_generic', 'group' => 'accounts', 'wizard' => 'other'),
		),
		'Hidden' => array(
			'graph' => array('title' => 'Graphs', 'table' => 'graphs', 'query' => ' AND is_removed=0'),
			'graph_pages' => array('title' => 'Graph page', 'table' => 'graph_pages', 'group' => 'graph_pages', 'query' => ' AND is_removed=0'),
			'summaries' => array('title' => 'Currency summaries', 'table' => 'summaries', 'group' => 'summaries'),
		),
	);
	foreach ($data['Exchanges'] as $key => $row) {
		$data['Exchanges'][$key]['title'] = get_exchange_name($key) . (isset($row['suffix']) ? $row['suffix'] : "") . " " . $row['label'] . "s";
	}
	foreach ($data['Mining pools'] as $key => $row) {
		$data['Mining pools'][$key]['title'] = get_exchange_name($key) . (isset($row['suffix']) ? $row['suffix'] : "") . " " . $row['label'] . "s";
	}
	return $data;
}

// we can't get this from account_data_grouped() because this also includes ticker information
function get_external_apis() {
	$external_apis = array(
		"Address balances" => array(
			// plaintext content is obtained by removing all HTML tags from the link HTML
			'blockchain' => '<a href="http://blockchain.info">Blockchain</a>',
			'litecoin' => '<a href="http://explorer.litecoin.net">Litecoin explorer</a>',
			'litecoin_block' => '<a href="http://explorer.litecoin.net">Litecoin explorer</a> (block count)',
			'feathercoin' => '<a href="http://cryptocoinexplorer.com:5750/">CryptoCoin explorer</a> (FTC)',
			'feathercoin_block' => '<a href="http://cryptocoinexplorer.com:5750/">CryptoCoin explorer</a> (FTC block count)',
			'ppcoin' => '<a href="http://ppc.cryptocoinexplorer.com/">CryptoCoin explorer</a> (PPC)',
			'ppcoin_block' => '<a href="http://ppc.cryptocoinexplorer.com/">CryptoCoin explorer</a> (PPC block count)',
		),

		"Mining pool wallets" => array(
			// TODO should be sorted
			'poolx' => '<a href="http://pool-x.eu">Pool-x.eu</a>',
			'slush' => '<a href="https://mining.bitcoin.cz">Slush\'s pool</a>',
			'wemineltc' => '<a href="https://www.wemineltc.com">WeMineLTC</a>',
			'givemecoins' => '<a href="https://www.give-me-coins.com">Give Me Coins</a>',
			'btcguild' => '<a href="https://www.btcguild.com">BTC Guild</a>',
			'50btc' => '<a href="https://www.50btc.com">50BTC</a>',
			'hypernova' => '<a href="https://hypernova.pw/">Hypernova</a>',
			'ltcmineru' => '<a href="http://ltcmine.ru/">LTCMine.ru</a>',
			'miningforeman' => '<a href="http://www.mining-foreman.org/">Mining Foreman</a> (LTC)',
			'miningforeman_ftc' => '<a href="http://ftc.mining-foreman.org/">Mining Foreman</a> (FTC)',
			'bitminter' => '<a href="https://bitminter.com/">BitMinter</a>',
			'liteguardian' => '<a href="https://www.liteguardian.com/">LiteGuardian</a>',
		),

		"Exchange wallets" => array(
			// TODO should be sorted
			'mtgox' => '<a href="http://mtgox.com">Mt.Gox</a>',
			'vircurex' => '<a href="http://vircurex.com">Vircurex</a>',
			'btce' => '<a href="http://btc-e.com">BTC-e</a>',
			'litecoinglobal' => '<a href="http://litecoinglobal.com">Litecoin Global</a>',
			'btct' => '<a href="http://btct.co">BTC Trading Co.</a>',
			'cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a>',
			'bips' => '<a href="https://bips.me">BIPS</a>',
			'havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a>',
		),

		"Exchange tickers" => array(
			// TODO should be sorted
			'ticker_mtgox' => '<a href="http://mtgox.com">Mt.Gox</a>',
			'ticker_btce' => '<a href="http://btc-e.com">BTC-e</a>',
			'ticker_bitnz' => '<a href="http://bitnz.com">BitNZ</a>',
			'ticker_vircurex' => '<a href="http://vircurex.com">Vircurex</a>',
			'ticker_themoneyconverter' => '<a href="http://themoneyconverter.com">TheMoneyConverter</a>',
			'ticker_virtex' => '<a href="https://www.cavirtex.com/">VirtEx</a>',
			'ticker_bitstamp' => '<a href="https://www.bitstamp.net/">Bitstamp</a>',
			'securities_litecoinglobal' => '<a href="http://litecoinglobal.com">Litecoin Global</a>',
			'securities_btct' => '<a href="http://btct.co">BTC Trading Co.</a>',
			'securities_cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a>',
			'securities_havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a>',
			'securities_update' => 'Securities list',
		),

		"Other" => array(
			// 'generic' => "Generic API balances",
			'outstanding' => '<a href="' . htmlspecialchars(url_for('premium')) . '">Premium account</a> processing',
		),
	);
	return $external_apis;
}

/**
 * Return a list of external API status keys to external API status titles;
 * titles are obtained by stripping HTML. It might be better to refactor this
 * so that titles are the default and HTML is added later.
 */
function get_external_apis_titles() {
	$apis = get_external_apis();
	$result = array();
	foreach ($apis as $group => $data) {
		foreach ($data as $key => $title) {
			$result[$key] = preg_replace('#<[^>]+?>#im', '', $title) . translate_external_api_group_to_suffix($group) . " API";
		}
	}
	// sort by title
	asort($result);
	return $result;
}

function translate_external_api_group_to_suffix($group) {
	// TODO use keys, not text
	switch ($group) {
		case "Address balances":
			return "";

		case "Mining pool wallets":
		case "Exchange wallets":
			return " wallet";

		case "Exchange tickers":
			return " ticker";

		case "Other":
			return "";	// nothing

		default:
			return "";	// nothing
	}
}

function get_blockchain_wizard_config($currency) {
	switch ($currency) {
		case "btc":
			return array(
				'premium_group' => 'blockchain',
				'title' => 'BTC address',
				'titles' => 'BTC addresses',
				'table' => 'addresses',
				'currency' => 'btc',
				'callback' => 'is_valid_btc_address',
				'url' => 'accounts_blockchain',
				'job_type' => 'blockchain',
				'address_callback' => 'btc_address',
				'client' => 'Bitcoin-Qt',
				'csv_kb' => 'bitcoin_csv',
			);

		case "ltc":
			return array(
				'premium_group' => 'litecoin',
				'title' => 'LTC address',
				'titles' => 'LTC addresses',
				'table' => 'addresses',
				'currency' => 'ltc',
				'callback' => 'is_valid_ltc_address',
				'url' => 'accounts_litecoin',
				'job_type' => 'litecoin',
				'address_callback' => 'ltc_address',
				'client' => 'Litecoin-Qt',
				'csv_kb' => 'litecoin_csv',
			);

		case "ftc":
			return array(
				'premium_group' => 'feathercoin',
				'title' => 'FTC address',
				'titles' => 'FTC addresses',
				'table' => 'addresses',
				'currency' => 'ftc',
				'callback' => 'is_valid_ftc_address',
				'url' => 'accounts_feathercoin',
				'job_type' => 'feathercoin',
				'address_callback' => 'ftc_address',
			);

		case "ppc":
			return array(
				'premium_group' => 'ppcoin',
				'title' => 'PPC address',
				'titles' => 'PPC addresses',
				'table' => 'addresses',
				'currency' => 'ppc',
				'callback' => 'is_valid_ppc_address',
				'url' => 'accounts_ppcoin',
				'job_type' => 'ppcoin',
				'address_callback' => 'ppc_address',
			);

		default:
			throw new Exception("Unknown blockchain currency '$currency'");
	}
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
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "6" || substr($address, 0, 1) == "7")
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}

function is_valid_ppc_address($address) {
	// based on is_valid_btc_address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "P")
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

function is_valid_havelock_apikey($key) {
	// not sure what the format is, but it looks to be 64 characters of random alphanumeric
	return preg_match("#^[0-9A-Za-z]{64}$#", $key);
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

function is_valid_ltcmineru_apikey($key) {
	// looks like a username, followed by 32 character hex string
	return preg_match("#^.+_[a-f0-9]{32}$#", $key);
}

function is_valid_generic_key($key) {
	// this could probably be in any format but should be at least one character
	return strlen($key) >= 1 && strlen($key) <= 255;
}

function is_valid_bitminter_apikey($key) {
	// looks like a 32 character alphanumeric uppercase string
	return strlen($key) == 32 && preg_match("#^[A-Z0-9]+$#", $key);
}

function is_valid_liteguardian_apikey($key) {
	// looks like 'api', followed by 32 character hex string
	return preg_match("#^api[a-f0-9]{32}$#", $key);
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

