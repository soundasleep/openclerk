<?php

/**
 * Defines all of the interesting properties of the web appliation:
 * what currencies are supported, what pairs, etc.
 */

function get_all_currencies() {
	return array("btc", "ltc", "nmc", "ppc", "ftc", "nvc", "usd", "eur", "cad", "aud", "nzd", "ghs");
}

function get_all_hashrate_currencies() {
	return array("btc", "ltc", "nmc");
}

function get_new_supported_currencies() {
	return array("ghs");
}

function get_all_cryptocurrencies() {
	return array("btc", "ltc", "nmc", "ppc", "ftc", "nvc");
}

function get_all_commodity_currencies() {
	return array("ghs");
}

function get_all_fiat_currencies() {
	return array_diff(array_diff(get_all_currencies(), get_all_cryptocurrencies()), get_all_commodity_currencies());
}

// currencies which we can download balances using explorers etc
function get_address_currencies() {
	return array("btc", "ltc", "ppc", "ftc", "nvc");	// no NMC yet
}

function get_currency_name($n) {
	switch ($n) {
		case "btc":	return "Bitcoin";
		case "ltc":	return "Litecoin";
		case "ppc":	return "PPCoin";
		case "ftc": return "Feathercoin";
		case "nvc": return "Novacoin";
		case "nmc":	return "Namecoin";
		case "usd":	return "United States dollar";
		case "nzd":	return "New Zealand dollar";
		case "aud": return "Australian dollar";
		case "cad": return "Canadian dollar";
		case "eur": return "Euro";
		case "ghs": return "CEX.io GHS";
		default:	return "Unknown (" . htmlspecialchars($n) . ")";
	}
}

function get_blockchain_currencies() {
	return array(
		"Blockchain" => array('btc'),
		"Litecoin Explorer" => array('ltc'),
		"CryptoCoin Explorer" => array('ppc', 'nvc'),
		"Feathercoin Search" => array('ftc'),
	);
}

function get_all_exchanges() {
	return array(
		"bitnz" =>  		"BitNZ",
		"btce" =>  			"BTC-e",
		"mtgox" =>  		"Mt.Gox",
		"bips" => 			"BIPS",
		"litecoinglobal" =>  "Litecoin Global",
		"litecoinglobal_wallet" => "Litecoin Global (Wallet)",
		"litecoinglobal_securities" => "Litecoin Global (Securities)",
		"btct" =>  			"BTC Trading Co.",
		"btct_wallet" =>  	"BTC Trading Co. (Wallet)",
		"btct_securities" => "BTC Trading Co. (Securities)",
		"cryptostocks" =>  	"Cryptostocks",
		"cryptostocks_wallet" => "Cryptostocks (Wallet)",
		"cryptostocks_securities" => "Cryptostocks (Securities)",
		"bitfunder"			=> "BitFunder",
		"bitfunder_wallet"	=> "BitFunder (Wallet)",
		"bitfunder_securities" => "BitFunder (Securities)",
		"individual_litecoinglobal" => "Litecoin Global (Individual Securities)",
		"individual_btct" => "BTC Trading Co. (Individual Securities)",
		"individual_bitfunder" => "BitFunder (Individual Securities)",
		"individual_cryptostocks" => "Cryptostocks (Individual Securities)",
		"individual_havelock" => "Havelock Investments (Individual Securities)",
		"individual_crypto-trade" => "Crypto-Trade (Individual Securities)",
		"generic" => 		"Generic API",
		"offsets" => 		"Offsets",		// generic
		"blockchain" =>  	"Blockchain",	// generic
		"poolx" => 			"Pool-x.eu",
		"wemineltc" =>  	"WeMineLTC",
		"givemecoins" => 	"Give Me Coins",
		"vircurex" =>  		"Vircurex",
		"slush" => 			"Slush's pool",
		"btcguild" =>  		"BTC Guild",
		"50btc" =>  		"50BTC",
		"hypernova" => 		"Hypernova",
		"ltcmineru" => 		"LTCMine.ru",
		"miningforeman" =>  "Mining Foreman",	// LTC default
		"miningforeman_ftc" => "Mining Foreman",
		"khore" =>			"nvc.khore.org",
		"cexio" =>			"CEX.io",
		"crypto-trade" =>	"Crypto-Trade",
		"crypto-trade_securities" => "Crypto-Trade (Securities)",
		"havelock" => 		"Havelock Investments",
		"havelock_wallet" => "Havelock Investments (Wallet)",
		"havelock_securities" => "Havelock Investments (Securities)",
		"bitminter" => 		"BitMinter",
		"liteguardian" =>  	"LiteGuardian",
		"themoneyconverter" => "TheMoneyConverter",
		"virtex" => 		"VirtEx",
		"bitstamp" => 		"Bitstamp",
	);

}

function get_exchange_name($n) {
	$exchanges = get_all_exchanges();
	if (isset($exchanges[$n])) {
		return $exchanges[$n];
	}
	return "Unknown (" . htmlspecialchars($n) . "]";
}

function get_new_exchanges() {
	return array("crypto-trade", "cexio");
}

function get_exchange_pairs() {
	return array(
		"bitnz" => array(array('nzd', 'btc')),
		"btce" => array(array('btc', 'ltc'), array('usd', 'btc'), array('usd', 'ltc'), array('btc', 'nmc'), array('btc', 'ppc'), array('btc', 'ftc'), array('eur', 'btc'), array('usd', 'eur'), array('usd', 'nmc'), array('btc', 'nvc')),
		"mtgox" => array(array('usd', 'btc'), array('eur', 'btc'), array('aud', 'btc'), array('cad', 'btc')),
		"vircurex" => array(array('usd', 'btc'), array('btc', 'ltc'), array('usd', 'ltc'), array('btc', 'nmc'), array('btc', 'ppc'), array('btc', 'ftc'), array('usd', 'nmc'), array('ltc', 'nmc'), array('eur', 'btc'), array('btc', 'nvc')),
		"themoneyconverter" => array(array('usd', 'eur'), array('usd', 'aud'), array('usd', 'nzd'), array('usd', 'cad')),
		"virtex" => array(array('cad', 'btc')),
		"bitstamp" => array(array('usd', 'btc')),
		"cexio" => array(array('btc', 'ghs')),
		"crypto-trade" => array(array('usd', 'btc'), array('eur', 'btc'), array('usd', 'ltc'), array('eur', 'ltc'), array('btc', 'ltc'), array('usd', 'nmc'), array('btc', 'nmc'), array('usd', 'ppc'), array('btc', 'ppc'), array('usd', 'ftc'), array('btc', 'ftc')),
	);
}

function get_new_exchange_pairs() {
	return array(
		"cexio_btcghs",
		"crypto-trade_usdbtc",
		"crypto-trade_eurbtc",
		"crypto-trade_usdltc",
		"crypto-trade_eurltc",
		"crypto-trade_btcltc",
		"crypto-trade_usdnmc",
		"crypto-trade_btcnmc",
		"crypto-trade_usdppc",
		"crypto-trade_btcppc",
		"crypto-trade_usdftc",
		"crypto-trade_btcftc",
	);
}

function get_security_exchange_pairs() {
	return array(
		"litecoinglobal" => array('ltc'),
		"btct" => array('btc'),
		"cryptostocks" => array('btc', 'ltc'),
		"havelock" => array('btc'),
		"bitfunder" => array('btc'),
		"crypto-trade" => array('btc', 'ltc'),
	);
}

function get_security_exchange_tables() {
	return array(
		"litecoinglobal" => "securities_litecoinglobal",
		"btct" => "securities_btct",
		"cryptostocks" => "securities_cryptostocks",
		"havelock" => "securities_havelock",
		"bitfunder" => "securities_bitfunder",
		"crypto-trade" => "securities_cryptotrade",
	);
}

function get_new_security_exchanges() {
	return array("crypto-trade");
}

function get_supported_wallets() {
	return array(
		// alphabetically sorted, except for generic
		"50btc" => array('btc', 'hash'),
		"bips" => array('btc', 'usd'),
		"bitminter" => array('btc', 'nmc', 'hash'),
		"btce" => array('btc', 'ltc', 'nmc', 'usd', 'ftc', 'eur', 'ppc', 'nvc'),		// used in jobs/btce.php
		"btcguild" => array('btc', 'nmc', 'hash'),
		"btct" => array('btc'),
		"cryptostocks" => array('btc', 'ltc'),
		"crypto-trade" => array('usd', 'eur', 'btc', 'ltc', 'nmc', 'ftc', 'ppc'),
		"cexio" => array('btc', 'ghs'),
		"givemecoins" => array('ltc', 'btc', 'ftc', 'hash'),
		"havelock" => array('btc'),
		"hypernova" => array('ltc', 'hash'),
		"khore" => array('nvc', 'hash'),
		"litecoinglobal" => array('ltc'),
		"liteguardian" => array('ltc'),
		"ltcmineru" => array('ltc'),
		"mtgox" => array('btc', 'usd', 'eur', 'aud', 'cad'),
		"miningforeman" => array('ltc', 'ftc'),
		"poolx" => array('ltc', 'hash'),
		"slush" => array('btc', 'nmc', 'hash'),
		"vircurex" => array('btc', 'ltc', 'nmc', 'ftc', 'usd', 'eur', 'ppc', 'nvc'),		// used in jobs/vircurex.php
		"wemineltc" => array('ltc', 'hash'),
		"generic" => get_all_currencies(),
	);
}

function get_new_supported_wallets() {
	return array("crypto-trade");
}

function crypto_address($currency, $address) {
	switch ($currency) {
		case 'btc': return btc_address($address);
		case 'ltc': return ltc_address($address);
		case 'ftc': return ftc_address($address);
		case 'ppc': return ppc_address($address);
		case 'nvc': return nvc_address($address);
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
		'summary_nvc' => array('currency' => 'nvc', 'key' => 'nvc', 'title' => get_currency_name('nvc'), 'short_title' => 'NVC'),
		'summary_usd_btce' => array('currency' => 'usd', 'key' => 'usd_btce', 'title' => get_currency_name('usd') . " (converted through BTC-e)", 'short_title' => 'USD (BTC-E)', 'exchange' => 'btce'),
		'summary_usd_mtgox' => array('currency' => 'usd', 'key' => 'usd_mtgox', 'title' => get_currency_name('usd') . " (converted through Mt.Gox)", 'short_title' => 'USD (Mt.Gox)', 'exchange' => 'mtgox'),
		'summary_usd_vircurex' => array('currency' => 'usd', 'key' => 'usd_vircurex', 'title' => get_currency_name('usd') . " (converted through Vircurex)", 'short_title' => 'USD (Vircurex)', 'exchange' => 'virtex'),
		'summary_usd_bitstamp' => array('currency' => 'usd', 'key' => 'usd_bitstamp', 'title' => get_currency_name('usd') . " (converted through Bitstamp)", 'short_title' => 'USD (Bitstamp)', 'exchange' => 'bitstamp'),
		'summary_usd_crypto-trade' => array('currency' => 'usd', 'key' => 'usd_crypto-trade', 'title' => get_currency_name('usd') . " (converted through Crypto-Trade)", 'short_title' => 'USD (Crypto-Trade)', 'exchange' => 'crypto-trade'),
		'summary_nzd_bitnz' => array('currency' => 'nzd', 'key' => 'nzd', 'title' => get_currency_name('nzd'), 'short_title' => 'NZD', 'exchange' => 'bitnz'),
		'summary_eur_btce' => array('currency' => 'eur', 'key' => 'eur_btce', 'title' => get_currency_name('eur') . " (converted through BTC-e)", 'short_title' => 'EUR (BTC-E)', 'exchange' => 'btce'),
		'summary_eur_mtgox' => array('currency' => 'eur', 'key' => 'eur_mtgox', 'title' => get_currency_name('eur') . " (converted through Mt.Gox)", 'short_title' => 'EUR (Mt.Gox)', 'exchange' => 'mtgox'),
		'summary_eur_vircurex' => array('currency' => 'eur', 'key' => 'eur_vircurex', 'title' => get_currency_name('eur') . " (converted through Vircurex)", 'short_title' => 'EUR (Vircurex)', 'exchange' => 'vircurex'),
		'summary_eur_crypto-trade' => array('currency' => 'eur', 'key' => 'eur_crypto-trade', 'title' => get_currency_name('eur') . " (converted through Crypto-Trade)", 'short_title' => 'EUR (Crypto-Trade)', 'exchange' => 'crypto-trade'),
		'summary_aud_mtgox' => array('currency' => 'aud', 'key' => 'aud_mtgox', 'title' => get_currency_name('aud') . " (converted through Mt.Gox)", 'short_title' => 'AUD (Mt.Gox)', 'exchange' => 'mtgox'),
		'summary_cad_mtgox' => array('currency' => 'usd', 'key' => 'cad_mtgox', 'title' => get_currency_name('cad') . " (converted through Mt.Gox)", 'short_title' => 'CAD (Mt.Gox)', 'exchange' => 'mtgox'),
		'summary_cad_virtex' => array('currency' => 'usd', 'key' => 'cad_virtex', 'title' => get_currency_name('cad') . " (converted through VirtEx)", 'short_title' => 'CAD (VirtEx)', 'exchange' => 'virtex'),
		'summary_ghs' => array('currency' => 'ghs', 'key' => 'ghs', 'title' => get_currency_name('ghs'), 'short_title' => 'GHS'),
	);
}

// used in graphs/util.php for defining BTC equivalent defaults
// also used in wizard_currencies.php for default exchanges
// TODO use in other graphs for default exchanges
function get_default_currency_exchange($c) {
	switch ($c) {
		// cryptos
		case "ltc": return "btce";
		case "ftc": return "btce";
		case "ppc": return "btce";
		case "nmc": return "btce";
		case "nvc": return "btce";
		// fiats
		case "usd": return "mtgox";
		case "nzd": return "bitnz";
		case "eur": return "btce";
		case "aud": return "mtgox";
		case "cad": return "virtex";
		// commodities
		case "ghs": return "cexio";
		default: throw new Exception("Unknown currency to exchange into: $c");
	}
}

/**
 * Total conversions: all currencies to a single currency, where possible.
 * (e.g. there's no exchange defined yet that converts NZD -> USD)
 */
function get_total_conversion_summary_types() {
	return array(
		'nzd_bitnz' => array('currency' => 'nzd', 'title' => get_currency_name('nzd'), 'short_title' => 'NZD'),
		'usd_btce' => array('currency' => 'usd', 'title' => get_currency_name('usd') . " (converted through BTC-e)", 'short_title' => 'USD (BTC-E)'),
		'usd_mtgox' => array('currency' => 'usd', 'title' => get_currency_name('usd') . " (converted through Mt.Gox)", 'short_title' => 'USD (Mt.Gox)'),
		'usd_vircurex' => array('currency' => 'usd', 'title' => get_currency_name('usd') . " (converted through Vircurex)", 'short_title' => 'USD (Vircurex)'),
		'usd_bitstamp' => array('currency' => 'usd', 'title' => get_currency_name('usd') . " (converted through Bitstamp)", 'short_title' => 'USD (Bitstamp)'),
		'usd_crypto-trade' => array('currency' => 'usd', 'title' => get_currency_name('usd') . " (converted through Crypto-Trade)", 'short_title' => 'USD (Crypto-Trade)'),
		'eur_btce' => array('currency' => 'eur', 'title' => get_currency_name('eur') . " (converted through BTC-e)", 'short_title' => 'EUR (BTC-E)'),
		'eur_mtgox' => array('currency' => 'eur', 'title' => get_currency_name('eur') . " (converted through Mt.Gox)", 'short_title' => 'EUR (Mt.Gox)'),
		'eur_vircurex' => array('currency' => 'eur', 'title' => get_currency_name('eur') . " (converted through Vircurex)", 'short_title' => 'EUR (Vircurex)'),
		'eur_crypto-trade' => array('currency' => 'eur', 'title' => get_currency_name('eur') . " (converted through Crypto-Trade)", 'short_title' => 'EUR (Crypto-Trade)'),
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
		'nvc' => array('currency' => 'nvc', 'title' => get_currency_name('nvc'), 'short_title' => 'NVC'),
		'ghs' => array('currency' => 'ghs', 'title' => get_currency_name('ghs'), 'short_title' => 'GHS'),
	);
}

function account_data_grouped() {
	$data = array(
		'Addresses' => array(
			'blockchain' => array('title' => 'BTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'btc\'', 'wizard' => 'addresses', 'currency' => 'btc'),
			'litecoin' => array('title' => 'LTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'ltc\'', 'wizard' => 'addresses', 'currency' => 'ltc'),
			'feathercoin' => array('title' => 'FTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'ftc\'', 'wizard' => 'addresses', 'currency' => 'ftc'),
			'ppcoin' => array('title' => 'PPC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'ppc\'', 'wizard' => 'addresses', 'currency' => 'ppc'),
			'novacoin' => array('title' => 'NVC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'nvc\'', 'wizard' => 'addresses', 'currency' => 'nvc'),
		),
		'Mining pools' => array(
			'poolx' => array('table' => 'accounts_poolx', 'group' => 'accounts', 'wizard' => 'pools'),
			'slush' => array('table' => 'accounts_slush', 'group' => 'accounts', 'wizard' => 'pools'),
			'wemineltc' => array('table' => 'accounts_wemineltc', 'group' => 'accounts', 'wizard' => 'pools'),
			'givemecoins' => array('table' => 'accounts_givemecoins', 'group' => 'accounts', 'wizard' => 'pools'),
			'btcguild' => array('table' => 'accounts_btcguild', 'group' => 'accounts', 'wizard' => 'pools'),
			'50btc' => array('table' => 'accounts_50btc', 'group' => 'accounts', 'wizard' => 'pools'),
			'hypernova' => array('table' => 'accounts_hypernova', 'group' => 'accounts', 'wizard' => 'pools'),
			'ltcmineru' => array('table' => 'accounts_ltcmineru', 'group' => 'accounts', 'wizard' => 'pools'),
			'miningforeman' => array('table' => 'accounts_miningforeman', 'group' => 'accounts', 'suffix' => ' LTC', 'wizard' => 'pools'),
			'miningforeman_ftc' => array('table' => 'accounts_miningforeman_ftc', 'group' => 'accounts', 'suffix' => ' FTC', 'wizard' => 'pools'),
			'bitminter' => array('table' => 'accounts_bitminter', 'group' => 'accounts', 'wizard' => 'pools'),
			'liteguardian' => array('table' => 'accounts_liteguardian', 'group' => 'accounts', 'wizard' => 'pools'),
			'khore' => array('table' => 'accounts_khore', 'group' => 'accounts', 'wizard' => 'pools'),
		),
		'Exchanges' => array(
			'mtgox' => array('table' => 'accounts_mtgox', 'group' => 'accounts', 'wizard' => 'exchanges'),
			'btce' => array('table' => 'accounts_btce', 'group' => 'accounts', 'wizard' => 'exchanges'),
			'vircurex' => array('table' => 'accounts_vircurex', 'group' => 'accounts', 'wizard' => 'exchanges'),
			'bips' => array('table' => 'accounts_bips', 'group' => 'accounts', 'wizard' => 'exchanges'),
			'cexio' => array('table' => 'accounts_cexio', 'group' => 'accounts', 'wizard' => 'exchanges'),
			'crypto-trade' => array('table' => 'accounts_cryptotrade', 'group' => 'accounts', 'wizard' => 'exchanges'),
		),
		'Securities' => array(
			'litecoinglobal' => array('table' => 'accounts_litecoinglobal', 'group' => 'accounts', 'wizard' => 'securities'),
			'btct' => array('table' => 'accounts_btct', 'group' => 'accounts', 'wizard' => 'securities'),
			'cryptostocks' => array('table' => 'accounts_cryptostocks', 'group' => 'accounts', 'wizard' => 'securities'),
			'havelock' => array('table' => 'accounts_havelock', 'group' => 'accounts', 'wizard' => 'securities'),
			'bitfunder' => array('table' => 'accounts_bitfunder', 'group' => 'accounts', 'wizard' => 'securities'),
			'crypto-trade' => array('table' => 'accounts_cryptotrade', 'group' => 'accounts', 'wizard' => 'securities'),
		),
		'Individual Securities' => array(
			'individual_litecoinglobal' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_litecoinglobal', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'litecoinglobal'),
			'individual_btct' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_btct', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'btct'),
			'individual_cryptostocks' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_cryptostocks', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'cryptostocks'),
			'individual_havelock' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_havelock', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'havelock'),
			'individual_bitfunder' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_bitfunder', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'bitfunder'),
			'individual_crypto-trade' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_cryptotrade', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'crypto-trade'),
		),
		'Other' => array(
			'generic' => array('title' => 'Generic APIs', 'label' => 'API', 'table' => 'accounts_generic', 'group' => 'accounts', 'wizard' => 'other'),
		),
		'Hidden' => array(
			'graph' => array('title' => 'Graphs', 'table' => 'graphs', 'query' => ' AND is_removed=0'),
			'graph_pages' => array('title' => 'Graph page', 'table' => 'graph_pages', 'group' => 'graph_pages', 'query' => ' AND is_removed=0'),
			'summaries' => array('title' => 'Currency summaries', 'table' => 'summaries', 'group' => 'summaries'),
		),
	);
	foreach ($data as $key0 => $row0) {
		foreach ($row0 as $key => $row) {
			if (!isset($data[$key0][$key]['label'])) {
				$data[$key0][$key]['label'] = "account";
			}
			if (!isset($data[$key0][$key]['labels'])) {
				$data[$key0][$key]['labels'] = $data[$key0][$key]['label'] . "s";
			}
			if (!isset($data[$key0][$key]['title'])) {
				$data[$key0][$key]['title'] = get_exchange_name($key) . (isset($row['suffix']) ? $row['suffix'] : "") . " " . $data[$key0][$key]['labels'];
			}
		}
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
			'novacoin' => '<a href="http://nvc.cryptocoinexplorer.com/">CryptoCoin explorer</a> (NVC)',
			'novacoin_block' => '<a href="http://nvc.cryptocoinexplorer.com/">CryptoCoin explorer</a> (NVC block count)',
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
			'khore' => '<a href="https://nvc.khore.org/">nvc.khore.org</a>',
		),

		"Exchange wallets" => array(
			// TODO should be sorted
			'mtgox' => '<a href="http://mtgox.com">Mt.Gox</a>',
			'vircurex' => '<a href="http://vircurex.com">Vircurex</a>',
			'btce' => '<a href="http://btc-e.com">BTC-e</a>',
			'cexio' => '<a href="https://cex.io">CEX.io</a>',
			'litecoinglobal' => '<a href="http://litecoinglobal.com">Litecoin Global</a>',
			'btct' => '<a href="http://btct.co">BTC Trading Co.</a>',
			'cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a>',
			'bips' => '<a href="https://bips.me">BIPS</a>',
			'havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a>',
			'crypto-trade' => '<a href="https://www.crypto-trade.com">Crypto-Trade</a>',
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
			'ticker_cexio' => '<a href="https://cex.io">CEX.io</a>',
			'ticker_crypto-trade' => '<a href="https://crypto-trade.com">Crypto-Trade</a>',
		),

		"Security exchanges" => array(
			// TODO should be sorted
			'securities_litecoinglobal' => '<a href="http://litecoinglobal.com">Litecoin Global</a>',
			'securities_btct' => '<a href="http://btct.co">BTC Trading Co.</a>',
			'securities_cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a>',
			'securities_havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a>',
			'securities_crypto-trade' => '<a href="https://crypto-trade.com">Crypto-Trade</a>',
			'securities_update_btct' => '<a href="http://btct.co">BTC Trading Co.</a> Securities list',
			'securities_update_litecoinglobal' => '<a href="http://litecoinglobal.com">Litecoin Global</a> Securities list',
			'securities_update_cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a> Securities list',
			'securities_update_havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a> Securities list',
			'securities_update_bitfunder' => '<a href="https://bitfunder.com/">BitFunder</a> Securities list',
			'ticker_crypto-trade' => '<a href="https://crypto-trade.com">Crypto-Trade</a>',		// securities for crypto-trade are handled by the ticker_crypto-trade
		),

		"Individual securities" => array(
			// TODO should be sorted
			'individual_litecoinglobal' => '<a href="http://litecoinglobal.com">Litecoin Global</a>',
			'individual_btct' => '<a href="http://btct.co">BTC Trading Co.</a>',
			'individual_cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a>',
			'individual_havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a>',
			'individual_bitfunder' => '<a href="https://www.bitfunder.com">BitFunder</a>',
			'individual_crypto-trade' => '<a href="https://crypto-trade.com">Crypto-Trade</a>',
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
				'job_type' => 'ppcoin',
				'address_callback' => 'ppc_address',
			);

		case "nvc":
			return array(
				'premium_group' => 'novacoin',
				'title' => 'NVC address',
				'titles' => 'NVC addresses',
				'table' => 'addresses',
				'currency' => 'nvc',
				'callback' => 'is_valid_nvc_address',
				'job_type' => 'novacoin',
				'address_callback' => 'nvc_address',
			);

		default:
			throw new Exception("Unknown blockchain currency '$currency'");
	}
}

function get_accounts_wizard_config($exchange) {
	$result = get_accounts_wizard_config_basic($exchange);
	if (!isset($result['title'])) {
		$result['title'] = get_exchange_name($exchange) . " account";
	}
	if (!isset($result['titles'])) {
		$result['titles'] = $result['title'] . "s";
	}
	if (!isset($result['khash'])) {
		$result['khash'] = false;
	}
	$result['exchange'] = $exchange;
	return $result;
}

function get_accounts_wizard_config_basic($exchange) {
	switch ($exchange) {
		// --- mining pools ---
		case "poolx":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mmcfe_apikey'),
				),
				'table' => 'accounts_poolx',
				'khash' => true,
			);

		case "slush":
			return array(
				'inputs' => array(
					'api_token' => array('title' => 'API current token', 'callback' => 'is_valid_slush_apitoken'),
				),
				'table' => 'accounts_slush',
			);

		case "wemineltc":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mmcfe_apikey'),
				),
				'table' => 'accounts_wemineltc',
				'khash' => true,
			);

		case "givemecoins":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mmcfe_apikey'),
				),
				'table' => 'accounts_givemecoins',
				'khash' => true,
			);

		case "btcguild":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_btcguild_apikey'),
				),
				'table' => 'accounts_btcguild',
			);

		case "50btc":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_50btc_apikey'),
				),
				'table' => 'accounts_50btc',
			);

		case "hypernova":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_hypernova_apikey'),
				),
				'table' => 'accounts_hypernova',
				'khash' => true,
			);

		case "ltcmineru":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_ltcmineru_apikey'),
				),
				'table' => 'accounts_ltcmineru',
				'khash' => true,
			);

		case "miningforeman":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mmcfe_apikey'),
				),
				'table' => 'accounts_miningforeman',
				'title' => 'Mining Foreman LTC account',
				'khash' => true,
			);

		case "miningforeman_ftc":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mmcfe_apikey'),
				),
				'table' => 'accounts_miningforeman_ftc',
				'title' => 'Mining Foreman FTC account',
				'khash' => true,
				'title_key' => 'miningforeman',
			);

		case "bitminter":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_bitminter_apikey'),
				),
				'table' => 'accounts_bitminter',
			);

		case "liteguardian":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_liteguardian_apikey'),
				),
				'table' => 'accounts_liteguardian',
				'khash' => true,
			);

		case "khore":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_khore_apikey'),
				),
				'table' => 'accounts_khore',
				'khash' => true,
			);

		// --- exchanges ---
		case "mtgox":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mtgox_apikey'),
					'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_mtgox_apisecret', 'length' => 128),
				),
				'table' => 'accounts_mtgox',
				'title' => 'Mt.Gox account',
			);

		case "bips":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_bips_apikey'),
				),
				'table' => 'accounts_bips',
			);

		case "btce":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_btce_apikey'),
					'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_btce_apisecret'),
				),
				'table' => 'accounts_btce',
			);

		case "vircurex":
			return array(
				'inputs' => array(
					'api_username' => array('title' => 'Username', 'callback' => 'is_valid_vircurex_apiusername'),
					'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_vircurex_apisecret', 'length' => 128),
				),
				'table' => 'accounts_vircurex',
			);

		case "cexio":
			return array(
				'inputs' => array(
					'api_username' => array('title' => 'Username', 'callback' => 'is_valid_cexio_apiusername'),
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_cexio_apikey'),
					'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_cexio_apisecret', 'length' => 32),
				),
				'table' => 'accounts_cexio',
			);

		case "crypto-trade":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_cryptotrade_apikey'),
					'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_cryptotrade_apisecret'),
				),
				'table' => 'accounts_cryptotrade',
			);

		// --- securities ---
		case "btct":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'Read-Only API key', 'callback' => 'is_valid_btct_apikey'),
				),
				'table' => 'accounts_btct',
			);

		case "litecoinglobal":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'Read-Only API key', 'callback' => 'is_valid_litecoinglobal_apikey'),
				),
				'table' => 'accounts_litecoinglobal',
			);

		case "cryptostocks":
			return array(
				'inputs' => array(
					'api_email' => array('title' => 'Account e-mail', 'callback' => 'is_valid_generic_key'),
					'api_key_coin' => array('title' => 'get_coin_balances API key', 'callback' => 'is_valid_generic_key'),
					'api_key_share' => array('title' => 'get_share_balances API key', 'callback' => 'is_valid_generic_key'),
				),
				'table' => 'accounts_cryptostocks',
			);

		case "havelock":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_havelock_apikey'),
				),
				'table' => 'accounts_havelock',
			);

		case "bitfunder":
			return array(
				'inputs' => array(
					'btc_address' => array('title' => 'BTC Address', 'callback' => 'is_valid_btc_address'),
				),
				'table' => 'accounts_bitfunder',
			);

		// --- securities ---
		case "individual_litecoinglobal":
			return array(
				'inputs' => array(
					'quantity' => array('title' => 'Quantity', 'callback' => 'is_valid_quantity'),
					'security_id' => array('title' => 'Security', 'dropdown' => 'dropdown_get_litecoinglobal_securities', 'callback' => 'is_valid_id'),
				),
				'table' => 'accounts_individual_litecoinglobal',
			);

		case "individual_btct":
			return array(
				'inputs' => array(
					'quantity' => array('title' => 'Quantity', 'callback' => 'is_valid_quantity'),
					'security_id' => array('title' => 'Security', 'dropdown' => 'dropdown_get_btct_securities', 'callback' => 'is_valid_id'),
				),
				'table' => 'accounts_individual_btct',
			);

		case "individual_bitfunder":
			return array(
				'inputs' => array(
					'quantity' => array('title' => 'Quantity', 'callback' => 'is_valid_quantity'),
					'security_id' => array('title' => 'Security', 'dropdown' => 'dropdown_get_bitfunder_securities', 'callback' => 'is_valid_id'),
				),
				'table' => 'accounts_individual_bitfunder',
			);

		case "individual_cryptostocks":
			return array(
				'inputs' => array(
					'quantity' => array('title' => 'Quantity', 'callback' => 'is_valid_quantity'),
					'security_id' => array('title' => 'Security', 'dropdown' => 'dropdown_get_cryptostocks_securities', 'callback' => 'is_valid_id'),
				),
				'table' => 'accounts_individual_cryptostocks',
			);

		case "individual_havelock":
			return array(
				'inputs' => array(
					'quantity' => array('title' => 'Quantity', 'callback' => 'is_valid_quantity'),
					'security_id' => array('title' => 'Security', 'dropdown' => 'dropdown_get_havelock_securities', 'callback' => 'is_valid_id'),
				),
				'table' => 'accounts_individual_havelock',
			);

		case "individual_crypto-trade":
			return array(
				'inputs' => array(
					'quantity' => array('title' => 'Quantity', 'callback' => 'is_valid_quantity'),
					'security_id' => array('title' => 'Security', 'dropdown' => 'dropdown_get_cryptotrade_securities', 'callback' => 'is_valid_id'),
				),
				'table' => 'accounts_individual_cryptotrade',
			);

		// --- other ---
		case "generic":
			return array(
				'inputs' => array(
					'api_url' => array('title' => 'URL', 'callback' => 'is_valid_generic_url'),
					'currency' => array('title' => 'Currency', 'dropdown' => 'dropdown_currency_list', 'callback' => 'is_valid_currency', 'style_prefix' => 'currency_name_'),
				),
				'table' => 'accounts_generic',
				'title' => 'Generic API',
			);

		default:
			throw new Exception("Unknown accounts type '$exchange'");
	}
}

// this function is in crypto.php so we can use just one wizard_accounts_callback rather than needing wizard_accounts_exchanges_callback (etc)
function get_wizard_account_type($wizard) {
	switch ($wizard) {
		case "exchanges":
			$account_type = array(
				'title' => 'Exchange',
				'titles' => 'Exchanges',
				'wizard' => 'exchanges',
				'hashrate' => false,
				'url' => 'wizard_accounts_exchanges',
			);
			break;

		case "pools":
			$account_type = array(
				'title' => 'Mining Pool',
				'titles' => 'Mining Pools',
				'wizard' => 'pools',
				'hashrate' => true,
				'url' => 'wizard_accounts_pools',
			);
			break;

		case "securities":
			$account_type = array(
				'title' => 'Securities Exchange',
				'titles' => 'Securities Exchanges',
				'wizard' => 'securities',
				'hashrate' => false,
				'url' => 'wizard_accounts_securities',
			);
			break;

		case "individual":
			$account_type = array(
				'title' => 'Individual Security',
				'titles' => 'Individual Securities',
				'accounts' => 'securities',
				'wizard' => 'individual',
				'hashrate' => false,
				'url' => 'wizard_accounts_individual_securities',
				'first_heading' => 'Exchange',
				'display_headings' => array('Security', 'Quantity'),
				'display_callback' => 'get_individual_security_config',
			);
			break;

		case "other":
			$account_type = array(
				'title' => 'Other Account',
				'titles' => 'Other Accounts',
				'wizard' => 'other',
				'hashrate' => false,
				'url' => 'wizard_accounts_other',
			);
			break;

		default:
			throw new Exception("Unknown wizard type '" . htmlspecialchars($wizard) . "'");
	}

	if (!isset($account_type['display_headings'])) {
		$account_type['display_headings'] = array();
	}
	if (!isset($account_type['display_callback'])) {
		$account_type['display_callback'] = false;
	}
	if (!isset($account_type['first_heading'])) {
		$account_type['first_heading'] = $account_type['title'];
	}
	if (!isset($account_type['accounts'])) {
		$account_type['accounts'] = "accounts";
	}

	return $account_type;
}

function get_individual_security_config($account) {
	$security = "(unknown exchange)";
	$securities = false;
	$historical_key = false;
	switch ($account['exchange']) {
		case "individual_litecoinglobal":
			$securities = dropdown_get_litecoinglobal_securities();
			$historical_key = 'securities_litecoinglobal_ltc';
			break;
		case "individual_btct":
			$securities = dropdown_get_btct_securities();
			$historical_key = 'securities_btct_btc';
			break;
		case "individual_bitfunder":
			$securities = dropdown_get_bitfunder_securities();
			$historical_key = 'securities_bitfunder_btc';
			break;
		case "individual_havelock":
			$securities = dropdown_get_havelock_securities();
			$historical_key = 'securities_havelock_btc';
			break;
		case "individual_cryptostocks":
			$securities = dropdown_get_cryptostocks_securities();
			break;
		case "individual_crypto-trade":
			$securities = dropdown_get_cryptotrade_securities();
			break;
	}

	if ($securities) {
		if (isset($securities[$account['security_id']])) {
			if ($historical_key) {
				$security = "<a href=\"" . htmlspecialchars(url_for('historical', array('id' => $historical_key, 'days' => 180, 'name' => $securities[$account['security_id']]))) . "\">" . htmlspecialchars($securities[$account['security_id']]) . "</a>";
			} else {
				$security = htmlspecialchars($securities[$account['security_id']]);
			}
		} else {
			$security = "(unknown security " . htmlspecialchars($account['security_id']) . ")";
		}
	}

	return array(
		$security,
		number_format($account['quantity']),
	);
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

	$q = db()->prepare("UPDATE users SET preferred_crypto=?, preferred_fiat=? WHERE id=?");
	$q->execute(array('btc', 'usd', $user_id));

	reset_user_graphs($user_id);

}

function reset_user_graphs($user_id) {

	// delete all graphs and graph pages
	$q = db()->prepare("DELETE FROM graphs WHERE page_id IN (SELECT id AS page_id FROM graph_pages WHERE user_id=?)");
	$q->execute(array($user_id));

	$q = db()->prepare("DELETE FROM graph_pages WHERE user_id=?");
	$q->execute(array($user_id));

	// set the user preferences to 'auto'
	// and request graph updating
	$q = db()->prepare("UPDATE users SET needs_managed_update=1, graph_managed_type=? WHERE id=?");
	$q->execute(array('auto', $user_id));

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

function dropdown_get_litecoinglobal_securities() {
	return dropdown_get_all_securities('securities_litecoinglobal');
}

function dropdown_get_btct_securities() {
	return dropdown_get_all_securities('securities_btct');
}

function dropdown_get_bitfunder_securities() {
	return dropdown_get_all_securities('securities_bitfunder');
}

function dropdown_get_cryptostocks_securities() {
	return dropdown_get_all_securities('securities_cryptostocks');
}

function dropdown_get_havelock_securities() {
	return dropdown_get_all_securities('securities_havelock');
}

function dropdown_get_cryptotrade_securities() {
	return dropdown_get_all_securities('securities_cryptotrade' /* table */);
}

/**
 * Returns an array of (id => security name).
 * Cached across calls.
 */
$dropdown_get_all_securities = array();
function dropdown_get_all_securities($table) {
	global $dropdown_get_all_securities;
	if (!isset($dropdown_get_all_securities[$table])) {
		$dropdown_get_all_securities[$table] = array();
		$q = db()->prepare("SELECT * FROM " . $table);
		$q->execute();
		while ($sec = $q->fetch()) {
			$dropdown_get_all_securities[$table][$sec['id']] = $sec['name'];
		}
	}
	return $dropdown_get_all_securities[$table];
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

function is_valid_nvc_address($address) {
	// based on is_valid_btc_address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "4")
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

function is_valid_khore_apikey($key) {
	// looks like a 64 character hex string
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_cexio_apikey($key) {
	// looks like a 24-32 character alphanumeric mixed case string
	return strlen($key) >= 24 && strlen($key) <= 32 && preg_match("#^[A-Za-z0-9]+$#", $key);
}

function is_valid_cexio_apisecret($key) {
	// looks like a 24-32 character alphanumeric mixed case string
	return strlen($key) >= 24 && strlen($key) <= 32 && preg_match("#^[A-Za-z0-9]+$#", $key);
}

function is_valid_cexio_apiusername($key) {
	// this could probably be in any format but should be at least one character
	return strlen($key) >= 1 && strlen($key) <= 255;
}

function is_valid_cryptotrade_apikey($key) {
	// guessing the format
	return preg_match("#^[A-F0-9]{8}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{12}$#", $key);
}

function is_valid_cryptotrade_apisecret($key) {
	// looks like a 40 character hex string
	return strlen($key) == 40 && preg_match("#^[a-f0-9]+$#", $key);
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

function is_valid_quantity($n) {
	return is_numeric($n) && $n == (int) $n && $n > 0;
}

function is_valid_id($n) {
	return is_numeric($n) && $n == (int) $n && $n > 0;
}

