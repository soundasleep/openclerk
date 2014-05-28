<?php

/**
 * Defines all of the interesting properties of the web appliation:
 * what currencies are supported, what pairs, etc.
 */

function get_all_currencies() {
	return array(
		"btc", "ltc", "nmc", "ppc", "ftc", "xpm", "nvc", "trc", "dog", "mec", "xrp", "dgc", "wdc", "ixc", "vtc", "net", "hbn",
		"usd", "gbp", "eur", "cad", "aud", "nzd", "cny", "pln", "ils", "krw",
		"ghs",
	);
}

function get_all_hashrate_currencies() {
	return array("btc", "ltc", "nmc", "nvc", "dog", "ftc", "mec", "dgc", "wdc", "ixc", "vtc", "net", "hbn");
}

// return true if this currency is a SHA256 currency and measured in MH/s rather than KH/s
function is_hashrate_mhash($cur) {
	return $cur == 'btc' || $cur == 'nmc' || $cur == 'ppc' || $cur == 'trc';
}

function get_new_supported_currencies() {
	return array("krw");
}

function get_all_cryptocurrencies() {
	return array("btc", "ltc", "nmc", "ppc", "ftc", "nvc", "xpm", "trc", "dog", "mec", "xrp" /* I guess xrp is a cryptocurrency */, "dgc", "wdc", "ixc", "vtc", "net", "hbn");
}

function get_all_commodity_currencies() {
	return array("ghs");
}

function get_all_fiat_currencies() {
	return array_diff(array_diff(get_all_currencies(), get_all_cryptocurrencies()), get_all_commodity_currencies());
}
function is_fiat_currency($cur) {
	return in_array($cur, get_all_fiat_currencies());
}

// currencies which we can download balances using explorers etc
function get_address_currencies() {
	return array("btc", "ltc", "nmc", "ppc", "ftc", "nvc", "xpm", "trc", "dog", "mec", "xrp", "dgc", "wdc", "ixc", "vtc", "net", "hbn");
}

function get_currency_name($n) {
	switch ($n) {
		case "btc":	return "Bitcoin";
		case "ltc":	return "Litecoin";
		case "ppc":	return "PPCoin";
		case "ftc": return "Feathercoin";
		case "nvc": return "Novacoin";
		case "nmc":	return "Namecoin";
		case "xpm":	return "Primecoin";
		case "trc":	return "Terracoin";
		case "dog":	return "Dogecoin";
		case "mec":	return "Megacoin";
		case "xrp": return "Ripple";
		case "dgc": return "Digitalcoin";
		case "wdc": return "Worldcoin";
		case "ixc": return "Ixcoin";
		case "vtc": return "Vertcoin";
		case "net": return "Netcoin";
		case "hbn": return "Hobonickels";

		case "usd":	return "United States dollar";
		case "nzd":	return "New Zealand dollar";
		case "aud": return "Australian dollar";
		case "cad": return "Canadian dollar";
		case "cny": return "Chinese yuan";
		case "pln": return "Polish zloty";	// not unicode! should be -l
		case "eur": return "Euro";
		case "gbp":	return "Pound sterling";
		case "ils":	return "Israeli new shekel";
		case "krw": return "South Korean won";

		case "ghs": return "CEX.io GHS";
		default:	return "Unknown (" . htmlspecialchars($n) . ")";
	}
}

function get_currency_abbr($c) {
	if ($c == "dog") return "DOGE";
	return strtoupper($c);
}

/**
 * Reverse of {@link get_currency_abbr()}.
 */
function get_currency_key($c) {
	if (strtolower($c) == "doge") return "dog";
	return strtolower($c);
}

function get_blockchain_currencies() {
	return array(
		"Blockchain" => array('btc'),
		"Litecoin Explorer" => array('ltc'),
		"CryptoCoin Explorer" => array('ppc', 'nvc', 'xpm', 'trc', 'dgc', 'wdc'),
		"Feathercoin Search" => array('ftc'),
		"DogeChain" => array('dog'),
		"192.241.222.65" => array('nmc'),
		"Ripple" => array('xrp'),
		"Megacoin Block Explorer" => array('mec'),
		"Altcoin Explorer" => array('ixc'),
		"VERTExplorer" => array('vtc'),
		"Netcoin Explorer" => array('net'),
		"162.217.249.198" => array('hbn'),
	);
}

function get_all_exchanges() {
	return array(
		"bit2c" => 			"Bit2c",
		"bitnz" =>  		"BitNZ",
		"btce" =>  			"BTC-e",
		"mtgox" =>  		"Mt.Gox",
		"bips" => 			"BIPS",		// this is now disabled
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
		"individual_796" => "796 Xchange (Individual Securities)",
		"generic" => 		"Generic API",
		"offsets" => 		"Offsets",		// generic
		"blockchain" =>  	"Blockchain",	// generic
		"poolx" => 			"Pool-x.eu",
		"wemineltc" =>  	"WeMineLTC",
		"wemineftc" =>  	"WeMineFTC",
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
		"ghashio" =>		"GHash.io",
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
		"796" =>			"796 Xchange",
		"796_wallet" =>		"796 Xchange (Wallet)",
		"796_securities" => "796 Xchange (Securities)",
		"kattare" =>		"ltc.kattare.com",
		"btcchina" =>		"BTC China",
		"cryptsy" =>		"Cryptsy",
		"litepooleu" =>		"Litepool",
		"coinhuntr" =>		"CoinHuntr",
		"eligius" =>		"Eligius",
		"lite_coinpool" =>	"lite.coin-pool.com",
		"beeeeer" =>		"b(e^5)r.org",
		"litecoinpool" =>	"litecoinpool.org",
		"coins-e" => 		"Coins-E",
		"dogepoolpw" => 	"dogepool.pw",
		"elitistjerks" =>	"Elitist Jerks",
		"dogechainpool" =>	"Dogechain Pool",
		"hashfaster" =>		"HashFaster",	// for labels, accounts actually use hashfaster_cur
		"hashfaster_ltc" =>	"HashFaster",
		"hashfaster_ftc" =>	"HashFaster",
		"hashfaster_doge" => "HashFaster",
		"triplemining" =>	"TripleMining",
		"ozcoin" =>			"Ozcoin",	// for labels, accounts actually use hashfaster_cur
		"ozcoin_ltc" =>		"Ozcoin",
		"ozcoin_btc" =>		"Ozcoin",
		"scryptpools" =>	"scryptpools.com",
		"bitcurex" =>		"Bitcurex",	// both exchanges for tickers
		"bitcurex_pln" =>	"Bitcurex PLN",	// the exchange wallet
		"bitcurex_eur" =>	"Bitcurex EUR",	// the exchange wallet
		"justcoin" =>		"Justcoin",
		"multipool" =>		"Multipool",
		"ypool" =>			"ypool.net",
		"cryptsy" => 		"Cryptsy",
		"coinbase" =>		"Coinbase",
		"litecoininvest" => "Litecoininvest",
		"litecoininvest_wallet" => "Litecoininvest (Wallet)",
		"litecoininvest_securities" => "Litecoininvest (Securities)",
		"individual_litecoininvest" => "Litecoininvest (Individual Securities)",
		"btcinve" => "BTCInve",
		"btcinve_wallet" => "BTCInve (Wallet)",
		"btcinve_securities" => "BTCInve (Securities)",
		"individual_btcinve" => "BTCInve (Individual Securities)",
		"miningpoolco" =>	"MiningPool.co",
		"vaultofsatoshi" => "Vault of Satoshi",
		"smalltimeminer" => "Small Time Miner",
		"smalltimeminer_mec" => "Small Time Miner",
		"ecoining" => "Ecoining",
		"ecoining_ppc" => "Ecoining",
		"teamdoge" => "TeamDoge",
		"dedicatedpool" => "dedicatedpool.com",
		"dedicatedpool_doge" => "dedicatedpool.com",
		"nut2pools" => "Nut2Pools",
		"nut2pools_ftc" => "Nut2Pools",
		"shibepool" => "Shibe Pool",
		"cryptopools" => "CryptoPools",
		"cryptopools_dgc" => "CryptoPools",
		"d2" => "d2",
		"d2_wdc" => "d2",
		"scryptguild" => "ScryptGuild",
		"kraken" => "Kraken",
		"average" => "Market Average",
		"rapidhash" => "RapidHash",
		"rapidhash_doge" => "RapidHash",
		"rapidhash_vtc" => "RapidHash",
		"cryptotroll" => "Cryptotroll",
		"cryptotroll_doge" => "Cryptotroll",
		"bitmarket_pl" => "BitMarket.pl",
		"poloniex" => "Poloniex",
		"mintpal" => "MintPal",

		// for failing server jobs
		"securities_havelock" => "Havelock Investments security",
	);
}

function get_exchange_name($n) {
	$exchanges = get_all_exchanges();
	if (isset($exchanges[$n])) {
		return $exchanges[$n];
	}
	return "Unknown (" . htmlspecialchars($n) . "]";
}

// these are just new exchange pairs; not new exchange wallets
function get_new_exchanges() {
	return array("bitmarket_pl", "poloniex");
}

function get_exchange_pairs() {
	return array(
		// should be in alphabetical order
		"bit2c" => array(array('ils', 'btc'), array('ils', 'ltc'), array('btc', 'ltc')),
		"bitcurex" => array(array('pln', 'btc'), array('eur', 'btc')),
		"bitmarket_pl" => array(array('pln', 'btc'), array('pln', 'ltc'), array('pln', 'dog'), array('pln', 'ppc')),
		"bitnz" => array(array('nzd', 'btc')),
		"bitstamp" => array(array('usd', 'btc')),
		"btcchina" => array(array('cny', 'btc')),
		"btce" => array(array('btc', 'ltc'), array('usd', 'btc'), array('usd', 'ltc'), array('btc', 'nmc'), array('btc', 'ppc'),
				array('btc', 'ftc'), array('eur', 'btc'), array('usd', 'eur'), array('usd', 'nmc'), array('btc', 'nvc'),
				array('btc', 'xpm'), array('btc', 'trc'), array('gbp', 'btc'), array('gbp', 'ltc'), array('cny', 'btc'),
				array('cny', 'ltc'), array('usd', 'cny'), array('usd', 'gbp'), array('usd', 'nvc')),
		"cexio" => array(array('btc', 'ghs'), array('btc', 'ltc'), array('btc', 'nmc'), array('nmc', 'ghs')),
		"coinbase" => array(array('usd', 'btc'), array('eur', 'btc'), array('gbp', 'btc'), array('cad', 'btc'), array('aud', 'btc'), array('cny', 'btc'),
				array('pln', 'btc'), array('nzd', 'btc'), array('ils', 'btc'), array('krw', 'btc')),
		"coins-e" => array(array('btc', 'xpm'), array('btc', 'trc'), array('btc', 'ftc'), array('btc', 'ltc'), array('btc', 'ppc'),
				array('ltc', 'xpm'), array('xpm', 'ppc'), array('btc', 'dog'), array('btc', 'mec'), array('btc', 'wdc'),
				array('btc', 'nvc'), array('btc', 'dgc')),
		"crypto-trade" => array(array('usd', 'btc'), array('eur', 'btc'), array('usd', 'ltc'), array('eur', 'ltc'), array('btc', 'ltc'), array('usd', 'nmc'), array('btc', 'nmc'), array('usd', 'ppc'), array('btc', 'ppc'), array('usd', 'ftc'), array('btc', 'ftc'), array('btc', 'xpm'), array('btc', 'trc'), array('btc', 'dgc'), array('btc', 'wdc')),
		"cryptsy" => array(array('btc', 'ltc'), array('btc', 'ppc'), array('btc', 'ftc'), array('btc', 'nvc'), array('btc', 'xpm'),
				array('btc', 'trc'), array('btc', 'dog'), array('btc', 'mec'), array('ltc', 'mec'), array('btc', 'dgc'),
				array('ltc', 'dgc'), array('btc', 'wdc'), array('btc', 'nmc'), array('btc', 'ixc'), array('btc', 'vtc'),
				array('btc', 'net'), array('ltc', 'net'), array('btc', 'hbn')),
		"justcoin" => array(array('usd', 'btc'), array('eur', 'btc'), array('btc', 'ltc'), array('btc', 'xrp')),	// also (nok, btc)
		"kraken" => array(array('ltc', 'dog'), array('ltc', 'xrp'), array('eur', 'ltc'), array('krw', 'ltc'), array('usd', 'ltc'),
				array('nmc', 'dog'), array('nmc', 'xrp'), array('eur', 'nmc'), array('krw', 'nmc'), array('usd', 'nmc'),
				array('btc', 'ltc'), array('btc', 'nmc'), array('btc', 'dog'), array('btc', 'xrp'), array('eur', 'btc'), array('krw', 'btc'), array('usd', 'btc'),	// also [btc, ven]
				array('eur', 'dog'), array('eur', 'xrp'),
				array('krw', 'xrp'),
				array('usd', 'dog'), array('usd', 'xrp')),
		"mintpal" => array(array('btc', 'dog'), array('btc', 'ltc'), array('btc', 'vtc')),
		"mtgox" => array(array('usd', 'btc'), array('eur', 'btc'), array('aud', 'btc'), array('cad', 'btc'), array('cny', 'btc'), array('gbp', 'btc'), array('pln', 'btc')),
		"poloniex" => array(array('btc', 'dog'), array('btc', 'ltc'), array('btc', 'vtc'), array('btc', 'xpm'), array('btc', 'nmc'),
				array('btc', 'wdc'), array('btc', 'ppc'), array('btc', 'ixc')),		// also pts, mmc, ...
		"themoneyconverter" => array(array('usd', 'eur'), array('usd', 'aud'), array('usd', 'nzd'), array('usd', 'cad'),
				array('usd', 'cny'), array('usd', 'pln'), array('usd', 'gbp'), array('usd', 'ils')),
		"vaultofsatoshi" => array(
				array('usd', 'btc'), array('usd', 'ltc'), array('usd', 'ppc'), array('usd', 'dog'), array('usd', 'ftc'), array('usd', 'xpm'), array('usd', 'vtc'),
				array('cad', 'btc'), array('cad', 'ltc'), array('cad', 'ppc'), array('cad', 'dog'), array('cad', 'ftc'), array('cad', 'xpm'), array('cad', 'vtc'),
				// also qrk
		),
		"vircurex" => array(array('usd', 'btc'), array('btc', 'ltc'), array('usd', 'ltc'), array('btc', 'nmc'), array('btc', 'ppc'),
				array('btc', 'ftc'), array('usd', 'nmc'), array('ltc', 'nmc'), array('eur', 'btc'), array('btc', 'nvc'),
				array('btc', 'xpm'), array('btc', 'trc'), array('btc', 'dog'), array('btc', 'dgc'), array('btc', 'wdc'),
				array('btc', 'ixc'), array('btc', 'vtc')),
		"virtex" => array(array('cad', 'btc'), array('cad', 'ltc'), array('btc', 'ltc')),
	);
}

function get_new_exchange_pairs() {
	return array(
		"bitmarket_pl_plnbtc",
		"bitmarket_pl_plnltc",
		"bitmarket_pl_plndog",
		"bitmarket_pl_plnppc",
		"poloniex_btcdog",
		"poloniex_btcltc",
		"poloniex_btcvtc",
		"poloniex_btcxpm",
		"poloniex_btcnmc",
		"poloniex_btcwdc",
		"poloniex_btcppc",
		"poloniex_btcixc",
		"mintpal_btcltc",
		"mintpal_btcdog",
		"mintpal_btcvtc",
	);
}

function get_security_exchange_pairs() {
	return array(
		// should be in alphabetical order
		"796" => array('btc'),
		"bitfunder" => array('btc'),		// this is now disabled
		"btcinve" => array('btc'),
		"btct" => array('btc'),				// issue #93: this is now disabled
		"crypto-trade" => array('btc', 'ltc'),
		"cryptostocks" => array('btc', 'ltc'),
		"litecoinglobal" => array('ltc'),		// issue #93: this is now disabled
		"litecoininvest" => array('ltc'),
		"havelock" => array('btc'),
	);
}

function get_security_exchange_tables() {
	return array(
		"litecoinglobal" => "securities_litecoinglobal",	// issue #93: this is now disabled
		"btct" => "securities_btct",						// issue #93: this is now disabled
		"cryptostocks" => "securities_cryptostocks",
		"havelock" => "securities_havelock",
		"bitfunder" => "securities_bitfunder",				// this is now disabled
		"crypto-trade" => "securities_cryptotrade",
		"796" => "securities_796",
		"litecoininvest" => "securities_litecoininvest",
		"btcinve" => "securities_btcinve",
	);
}

function get_new_security_exchanges() {
	return array("litecoininvest", "btcinve");
}

function get_supported_wallets() {
	return array(
		// alphabetically sorted, except for generic
		"50btc" => array('btc', 'hash'),
		"796" => array('btc', 'ltc', 'usd'),
		"beeeeer" => array('xpm'),
		"bips" => array('btc', 'usd'),
		"bit2c" => array('btc', 'ltc', 'ils'),
		"bitcurex_eur" => array('btc', 'eur'),
		"bitcurex_pln" => array('btc', 'pln'),
		"bitmarket_pl" => array('btc', 'ltc', 'dog', 'ppc', 'pln'),
		"bitminter" => array('btc', 'nmc', 'hash'),
		"bitstamp" => array('btc', 'usd'),
		"btce" => array('btc', 'ltc', 'nmc', 'usd', 'ftc', 'eur', 'ppc', 'nvc', 'xpm', 'trc'),		// used in jobs/btce.php
		"btcguild" => array('btc', 'nmc', 'hash'),
		"btcinve" => array('btc'),
		"btct" => array('btc'),
		"coinbase" => array('btc'),
		"coinhuntr" => array('ltc', 'hash'),
		"cryptopools" => array('dgc', 'hash'),		// other coins available
		"cryptostocks" => array('btc', 'ltc'),
		"crypto-trade" => array('usd', 'eur', 'btc', 'ltc', 'nmc', 'ftc', 'ppc', 'xpm', 'trc', 'dgc', 'wdc'),
		"cryptotroll" => array('dog', 'hash'),
		"cryptsy" => array('btc', 'ltc', 'ppc', 'ftc', 'xpm', 'nvc', 'trc', 'dog', 'mec', 'ixc', 'nmc', 'wdc', 'dgc', 'vtc', 'net', 'hbn'),
		"cexio" => array('btc', 'ghs', 'nmc', 'ixc', 'ltc', 'dog', 'ftc'),		// also available: dvc
		"d2" => array('wdc', 'hash'),				// other coins available
		"dedicatedpool" => array('dog', 'hash'),		// other coins available
		"dogechainpool" => array('dog', 'hash'),
		"dogepoolpw" => array('dog', 'hash'),
		"ecoining" => array('ppc', 'hash'),
		"eligius" => array('btc', 'hash'),		// BTC is paid directly to BTC address but also stored temporarily
		"elitistjerks" => array('ltc', 'hash'),
		"ghashio" => array('hash'),		// we only use ghash.io for hashrates
		"givemecoins" => array('ltc', 'btc', 'ftc', 'hash'),
		"havelock" => array('btc'),
		"hashfaster" => array('ltc', 'ftc', 'dog', 'hash'),
		"hypernova" => array('ltc', 'hash'),
		"justcoin" => array('btc', 'ltc', 'usd', 'eur', 'xrp'),	 // supports btc, usd, eur, nok, ltc
		"khore" => array('nvc', 'hash'),
		"kraken" => array('btc', 'eur', 'ltc', 'nmc', 'usd', 'dog', 'xrp', 'krw'),		// also 'asset-based Ven/XVN'
		"litecoinpool" => array('ltc', 'hash'),
		"litecoininvest" => array('ltc'),
		"liteguardian" => array('ltc'),
		"litepooleu" => array('ltc', 'hash'),
		"kattare" => array('ltc', 'hash'),
		"ltcmineru" => array('ltc'),
		"mtgox" => array('btc', 'usd', 'eur', 'aud', 'cad', 'nzd', 'cny', 'gbp'),
		"miningforeman" => array('ltc', 'ftc', 'hash'),
		"miningpoolco" => array('dog', 'ltc', 'mec', 'hash'),		// and LOTS more; used in jobs/miningpoolco.php
		"multipool" => array('btc', 'ltc', 'dog', 'ftc', 'ltc', 'nvc', 'ppc', 'trc', 'mec', 'hash'),		// and LOTS more; used in jobs/multipool.php
		"nut2pools" => array('ftc', 'hash'),
		"ozcoin" => array('ltc', 'btc', 'hash'),
		"poloniex" => array('btc', 'ltc', 'dog', 'vtc', 'wdc', 'nmc', 'ppc', 'xpm', 'ixc'),		// and LOTS more; used in jobs/poloniex.php
		"poolx" => array('ltc', 'hash'),
		"rapidhash" => array('dog', 'vtc', 'hash'),
		"scryptpools" => array('dog', 'hash'),
		"scryptguild" => array('btc', 'dog', 'ltc', 'wdc', 'dgc', 'hash'),	// others available: lot, leaf, sbc, smc, meow, glc, eac, csc, anc
		"shibepool" => array('dog', 'hash'),
		"slush" => array('btc', 'nmc', 'hash'),
		"teamdoge" => array('dog', 'hash'),
		"triplemining" => array('btc', 'hash'),
		"vaultofsatoshi" => array('cad', 'usd', 'btc', 'ltc', 'ppc', 'dog', 'ftc', 'xpm', 'vtc'),		// used in jobs/vaultofsatoshi.php (also supports qrk)
		"vircurex" => array('btc', 'ltc', 'nmc', 'ftc', 'usd', 'eur', 'ppc', 'nvc', 'xpm', 'trc', 'dog', 'ixc', 'vtc'),		// used in jobs/vircurex.php
		"wemineftc" => array('ftc', 'hash'),
		"wemineltc" => array('ltc', 'hash'),
		"ypool" => array('ftc', 'xpm', 'dog'),	// also pts
		"generic" => get_all_currencies(),
	);
}

// get all supported wallets that are safe w.r.t. allow_unsafe
function get_supported_wallets_safe() {
	$wallets = get_supported_wallets();
	if (!get_site_config('allow_unsafe')) {
		foreach (account_data_grouped() as $label => $group) {
			foreach ($group as $exchange => $value) {
				if (isset($wallets[$exchange]) && $value['unsafe']) {
					unset($wallets[$exchange]);
				}
			}
		}
	}
	return $wallets;
}

function get_new_supported_wallets() {
	return array("bitmarket_pl");
}

function get_summary_types() {
	// add cryptocurrencies and commodity currencies automatically
	$summary_types = array();
	foreach (get_all_cryptocurrencies() as $cur) {
		$summary_types['summary_' . $cur] = array(
			'currency' => $cur,
			'key' => $cur,
			'title' => get_currency_name($cur),
			'short_title' => get_currency_abbr($cur),
		);
	}
	foreach (get_all_commodity_currencies() as $cur) {
		$summary_types['summary_' . $cur] = array(
			'currency' => $cur,
			'key' => $cur,
			'title' => get_currency_name($cur),
			'short_title' => get_currency_abbr($cur),
		);
	}

	// add fiat pairs automatically
	foreach (get_exchange_pairs() as $exchange => $pairs) {
		foreach ($pairs as $pair) {
			if ($pair[1] == 'btc') {
				// fiat currency
				$summary_types['summary_' . $pair[0] . '_' . $exchange] = array(
					'currency' => $pair[0],
					'key' => $pair[0] . '_' . $exchange,
					'title' => get_currency_name($pair[0]) . ' (converted through ' . get_exchange_name($exchange) . ')',
					'short_title' => get_currency_abbr($pair[0]) . ' (' . get_exchange_name($exchange) . ')',
					'exchange' => $exchange,
				);
			}
		}
	}

	// finally, add market averages for fiats
	// (if there is a result in the ticker_recent)
	foreach (get_all_fiat_currencies() as $cur) {
		$exchange = "average";
		$q = db()->prepare("SELECT * FROM ticker_recent WHERE currency1=? AND currency2=? AND exchange=? LIMIT 1");
		$q->execute(array($cur, 'btc', 'average'));
		if ($q->fetch()) {
			$summary_types['summary_' . $cur . '_' . $exchange] = array(
				'currency' => $cur,
				'key' => $cur . '_' . $exchange,
				'title' => get_currency_name($cur) . ' (converted using market average)',
				'short_title' => get_currency_abbr($cur) . ' (market average)',
				'exchange' => $exchange,
			);
		}
	}

	return $summary_types;

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
		case "xpm": return "btce";
		case "trc": return "btce";
		case "dog": return "coins-e";
		case "mec": return "cryptsy";
		case "xrp": return "justcoin";
		case "dgc": return "cryptsy";
		case "wdc": return "cryptsy";
		case "ixc": return "cryptsy";
		case "vtc": return "cryptsy";
		case "net": return "cryptsy";
		case "hbn": return "cryptsy";
		// fiats
		case "usd": return "bitstamp";
		case "nzd": return "bitnz";
		case "eur": return "btce";
		case "gbp": return "coinbase";
		case "aud": return "coinbase";
		case "cad": return "virtex";
		case "cny": return "btcchina";
		case "pln": return "bitcurex";
		case "ils": return "bit2c";
		case "krw": return "kraken";
		// commodities
		case "ghs": return "cexio";
		default: throw new Exception("Unknown currency to exchange into: $c");
	}
}

/**
 * Total conversions: all currencies to a single currency, where possible.
 * (e.g. there's no exchange defined yet that converts NZD -> USD)
 */
$global_get_total_conversion_summary_types = null;
function get_total_conversion_summary_types() {
	global $global_get_total_conversion_summary_types;
	if ($global_get_total_conversion_summary_types == null) {
		$summary_types = array();

		// add fiat pairs automatically
		foreach (get_exchange_pairs() as $exchange => $pairs) {
			foreach ($pairs as $pair) {
				if ($pair[1] == 'btc') {
					// fiat currency
					$summary_types[$pair[0] . '_' . $exchange] = array(
						'currency' => $pair[0],
						'title' => get_currency_name($pair[0]) . ' (converted through ' . get_exchange_name($exchange) . ')',
						'short_title' => get_currency_abbr($pair[0]) . ' (' . get_exchange_name($exchange) . ')',
						'exchange' => $exchange,
					);
				}
			}
		}

		// and also all average pairs for all fiats
		// (if there is a result in the ticker_recent)
		foreach (get_all_fiat_currencies() as $cur) {
			$exchange = "average";
			$q = db()->prepare("SELECT * FROM ticker_recent WHERE currency1=? AND currency2=? AND exchange=? LIMIT 1");
			$q->execute(array($cur, 'btc', 'average'));
			if ($q->fetch()) {
				$summary_types[$cur . '_' . $exchange] = array(
					'currency' => $cur,
					'title' => get_currency_name($cur) . ' (converted using market average)',
					'short_title' => get_currency_abbr($cur) . ' (market average)',
					'exchange' => $exchange,
				);
			}
		}

		// sort by currency order, then title
		uasort($summary_types, 'sort_get_total_conversion_summary_types');

		$global_get_total_conversion_summary_types = $summary_types;
	}
	return $global_get_total_conversion_summary_types;
}

function sort_get_total_conversion_summary_types($a, $b) {
	$order_a = array_search($a['currency'], get_all_currencies());
	$order_b = array_search($b['currency'], get_all_currencies());
	if ($order_a == $order_b) {
		return strcmp($a['short_title'], $b['short_title']);
	}
	return $order_a - $order_b;
}

/**
 * Crypto conversions: all cryptocurrencies to a single currency.
 */
function get_crypto_conversion_summary_types() {
	$currencies = get_all_cryptocurrencies() + get_all_commodity_currencies();
	$result = array();
	foreach ($currencies as $c) {
		$result[$c] = array(
			'currency' => $c,
			'title' => get_currency_name($c),
			'short_title' => get_currency_abbr($c),
		);
	}
	return $result;
}

function account_data_grouped() {
	$data = array(
		'Addresses' => array(
			'blockchain' => array('title' => 'BTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'btc\'', 'wizard' => 'addresses', 'currency' => 'btc'),
			'litecoin' => array('title' => 'LTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'ltc\'', 'wizard' => 'addresses', 'currency' => 'ltc'),
			'feathercoin' => array('title' => 'FTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'ftc\'', 'wizard' => 'addresses', 'currency' => 'ftc'),
			'ppcoin' => array('title' => 'PPC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'ppc\'', 'wizard' => 'addresses', 'currency' => 'ppc'),
			'novacoin' => array('title' => 'NVC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'nvc\'', 'wizard' => 'addresses', 'currency' => 'nvc'),
			'primecoin' => array('title' => 'XPM addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'xpm\'', 'wizard' => 'addresses', 'currency' => 'xpm'),
			'terracoin' => array('title' => 'TRC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'trc\'', 'wizard' => 'addresses', 'currency' => 'trc'),
			'dogecoin' => array('title' => 'DOGE addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'dog\'', 'wizard' => 'addresses', 'currency' => 'dog'),
			'megacoin' => array('title' => 'MEC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'mec\'', 'wizard' => 'addresses', 'currency' => 'mec'),
			'ripple' => array('title' => 'XRP addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'xrp\'', 'wizard' => 'addresses', 'currency' => 'xrp'),
			'namecoin' => array('title' => 'NMC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'nmc\'', 'wizard' => 'addresses', 'currency' => 'nmc'),
			'digitalcoin' => array('title' => 'DGC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'dgc\'', 'wizard' => 'addresses', 'currency' => 'dgc'),
			'worldcoin' => array('title' => 'WDC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'wdc\'', 'wizard' => 'addresses', 'currency' => 'wdc'),
			'ixcoin' => array('title' => 'IXC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'ixc\'', 'wizard' => 'addresses', 'currency' => 'ixc'),
			'vertcoin' => array('title' => 'VTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'vtc\'', 'wizard' => 'addresses', 'currency' => 'vtc'),
			'netcoin' => array('title' => 'NET addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'net\'', 'wizard' => 'addresses', 'currency' => 'net'),
			'hobonickels' => array('title' => 'HBN addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses', 'group' => 'addresses', 'query' => ' AND currency=\'hbn\'', 'wizard' => 'addresses', 'currency' => 'hbn'),
		),
		'Mining pools' => array(
			'50btc' => array('table' => 'accounts_50btc', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'beeeeer' => array('table' => 'accounts_beeeeer', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'bitminter' => array('table' => 'accounts_bitminter', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'btcguild' => array('table' => 'accounts_btcguild', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'coinhuntr' => array('table' => 'accounts_coinhuntr', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'cryptopools_dgc' => array('table' => 'accounts_cryptopools_dgc', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'cryptopools', 'suffix' => ' DGC'),
			'cryptotroll_doge' => array('table' => 'accounts_cryptotroll_doge', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'cryptotroll', 'suffix' => ' DOGE'),
			'd2_wdc' => array('table' => 'accounts_d2_wdc', 'group' => 'accounts', 'suffix' => ' WDC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'd2'),
			'dedicatedpool_doge' => array('table' => 'accounts_dedicatedpool_doge', 'group' => 'accounts', 'suffix' => ' DOGE', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'dedicatedpool'),
			'dogechainpool' => array('table' => 'accounts_dogechainpool', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'dogepoolpw' => array('table' => 'accounts_dogepoolpw', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'ecoining_ppc' => array('table' => 'accounts_ecoining_ppc', 'group' => 'accounts', 'suffix' => ' Peercoin', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'ecoining'),
			'eligius' => array('table' => 'accounts_eligius', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'elitistjerks' => array('table' => 'accounts_elitistjerks', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'ghashio' => array('table' => 'accounts_ghashio', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'givemecoins' => array('table' => 'accounts_givemecoins', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'hashfaster_doge' => array('table' => 'accounts_hashfaster_doge', 'group' => 'accounts', 'suffix' => ' DOGE', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'hashfaster'),
			'hashfaster_ftc' => array('table' => 'accounts_hashfaster_ftc', 'group' => 'accounts', 'suffix' => ' FTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'hashfaster'),
			'hashfaster_ltc' => array('table' => 'accounts_hashfaster_ltc', 'group' => 'accounts', 'suffix' => ' LTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'hashfaster'),
			'hypernova' => array('table' => 'accounts_hypernova', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'kattare' => array('table' => 'accounts_kattare', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'khore' => array('table' => 'accounts_khore', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'lite_coinpool' => array('table' => 'accounts_lite_coinpool', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true, 'disabled' => true),
			'litecoinpool' => array('table' => 'accounts_litecoinpool', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'liteguardian' => array('table' => 'accounts_liteguardian', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'litepooleu' => array('table' => 'accounts_litepooleu', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'ltcmineru' => array('table' => 'accounts_ltcmineru', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'miningforeman' => array('table' => 'accounts_miningforeman', 'group' => 'accounts', 'suffix' => ' LTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'miningforeman'),
			'miningforeman_ftc' => array('table' => 'accounts_miningforeman_ftc', 'group' => 'accounts', 'suffix' => ' FTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'miningforeman'),
			'miningpoolco' => array('table' => 'accounts_miningpoolco', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'multipool' => array('table' => 'accounts_multipool', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'nut2pools_ftc' => array('table' => 'accounts_nut2pools_ftc', 'group' => 'accounts', 'suffix' => ' FTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'nut2pools'),
			'ozcoin_btc' => array('table' => 'accounts_ozcoin_btc', 'group' => 'accounts', 'suffix' => ' BTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'ozcoin'),
			'ozcoin_ltc' => array('table' => 'accounts_ozcoin_ltc', 'group' => 'accounts', 'suffix' => ' LTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'ozcoin'),
			'poolx' => array('table' => 'accounts_poolx', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'rapidhash_doge' => array('table' => 'accounts_rapidhash_doge', 'group' => 'accounts', 'suffix' => ' DOGE', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'rapidhash'),
			'rapidhash_vtc' => array('table' => 'accounts_rapidhash_vtc', 'group' => 'accounts', 'suffix' => ' VTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'rapidhash'),
			'scryptguild' => array('table' => 'accounts_scryptguild', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'scryptpools' => array('table' => 'accounts_scryptpools', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'shibepool' => array('table' => 'accounts_shibepool', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'slush' => array('table' => 'accounts_slush', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'smalltimeminer_mec' => array('table' => 'accounts_smalltimeminer_mec', 'group' => 'accounts', 'suffix' => ' Megacoin', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'smalltimeminer', 'disabled' => true),
			'teamdoge' => array('table' => 'accounts_teamdoge', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'triplemining' => array('table' => 'accounts_triplemining', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'wemineftc' => array('table' => 'accounts_wemineftc', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'wemineltc' => array('table' => 'accounts_wemineltc', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
			'ypool' => array('table' => 'accounts_ypool', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
		),
		'Exchanges' => array(
			'bips' => array('table' => 'accounts_bips', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true, 'disabled' => true),
			'bit2c' => array('table' => 'accounts_bit2c', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
			'bitcurex_eur' => array('table' => 'accounts_bitcurex_eur', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
			'bitcurex_pln' => array('table' => 'accounts_bitcurex_pln', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
			'bitmarket_pl' => array('table' => 'accounts_bitmarket_pl', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
			'bitstamp' => array('table' => 'accounts_bitstamp', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
			'btce' => array('table' => 'accounts_btce', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
			'cexio' => array('table' => 'accounts_cexio', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
			'coinbase' => array('table' => 'accounts_coinbase', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
			'crypto-trade' => array('table' => 'accounts_cryptotrade', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
			'cryptsy' => array('table' => 'accounts_cryptsy', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
			'justcoin' => array('table' => 'accounts_justcoin', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
			'kraken' => array('table' => 'accounts_kraken', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
			'mtgox' => array('table' => 'accounts_mtgox', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
			'poloniex' => array('table' => 'accounts_poloniex', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
			'vaultofsatoshi' => array('table' => 'accounts_vaultofsatoshi', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
			'vircurex' => array('table' => 'accounts_vircurex', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
		),
		'Securities' => array(
			'796' => array('table' => 'accounts_796', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true),
			'bitfunder' => array('table' => 'accounts_bitfunder', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true, 'disabled' => true),
			'btcinve' => array('table' => 'accounts_btcinve', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true),
			'btct' => array('table' => 'accounts_btct', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true, 'disabled' => true),
			'crypto-trade' => array('table' => 'accounts_cryptotrade', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true),
			'cryptostocks' => array('table' => 'accounts_cryptostocks', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true),
			'havelock' => array('table' => 'accounts_havelock', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true),
			'litecoininvest' => array('table' => 'accounts_litecoininvest', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true),
			'litecoinglobal' => array('table' => 'accounts_litecoinglobal', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true, 'disabled' => true),
		),
		'Individual Securities' => array(
			'individual_796' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_796', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => '796', 'securities_table' => 'securities_796', 'failure' => true),
			'individual_bitfunder' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_bitfunder', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'bitfunder', 'securities_table' => 'securities_bitfunder', 'failure' => true, 'disabled' => true),
			'individual_btcinve' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_btcinve', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'btcinve', 'securities_table' => 'securities_btcinve', 'failure' => true),
			'individual_btct' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_btct', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'btct', 'securities_table' => 'securities_btct', 'failure' => true, 'disabled' => true),
			'individual_crypto-trade' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_cryptotrade', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'crypto-trade', 'securities_table' => 'securities_cryptotrade', 'failure' => true),
			'individual_cryptostocks' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_cryptostocks', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'cryptostocks', 'securities_table' => 'securities_cryptostocks', 'failure' => true),
			'individual_havelock' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_havelock', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'havelock', 'securities_table' => 'securities_havelock', 'failure' => true),
			'individual_litecoininvest' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_litecoininvest', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'litecoininvest', 'securities_table' => 'securities_litecoininvest', 'failure' => true),
			'individual_litecoinglobal' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_litecoinglobal', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'litecoinglobal', 'securities_table' => 'securities_litecoinglobal', 'failure' => true, 'disabled' => true),
		),
		'Finance' => array(
			'finance_accounts' => array('title' => 'Finance account', 'label' => 'finance account', 'table' => 'finance_accounts', 'group' => 'finance_accounts', 'job' => false),
			'finance_categories' => array('title' => 'Finance category', 'label' => 'finance category', 'titles' => 'finance categories', 'table' => 'finance_categories', 'group' => 'finance_categories', 'job' => false),
		),
		'Other' => array(
			'generic' => array('title' => 'Generic APIs', 'label' => 'API', 'table' => 'accounts_generic', 'group' => 'accounts', 'wizard' => 'other', 'failure' => true),
		),
		'Hidden' => array(
			'graph' => array('title' => 'Graphs', 'table' => 'graphs', 'query' => ' AND is_removed=0', 'job' => false),
			'graph_pages' => array('title' => 'Graph page', 'table' => 'graph_pages', 'group' => 'graph_pages', 'query' => ' AND is_removed=0', 'job' => false),
			'summaries' => array('title' => 'Currency summaries', 'table' => 'summaries', 'group' => 'summaries', 'job' => false),
			'notifications' => array('title' => 'Notifications', 'table' => 'notifications', 'group' => 'notifications', 'wizard' => 'notifications'),
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
			if (!isset($data[$key0][$key]['title_key'])) {
				$data[$key0][$key]['title_key'] = $key;
			}
			if (!isset($data[$key0][$key]['failure'])) {
				$data[$key0][$key]['failure'] = false;
			}
			if (!isset($data[$key0][$key]['job'])) {
				$data[$key0][$key]['job'] = true;
			}
			if (!isset($data[$key0][$key]['disabled'])) {
				$data[$key0][$key]['disabled'] = false;
			}
			if (!isset($data[$key0][$key]['unsafe'])) {
				$data[$key0][$key]['unsafe'] = false;
			}
			if (!isset($data[$key0][$key]['suffix'])) {
				$data[$key0][$key]['suffix'] = false;
			}
		}
	}
	return $data;
}

/**
 * @return the account data as an array, or {@code false} if no account type could be found (and {@code throw_exception_on_failure} is {@code false})
 * @throws Exception if {@code throw_exception_on_failure} is {@code true} and no account type could be found
 */
function get_account_data($exchange, $throw_exception_on_failure = true) {
	foreach (account_data_grouped() as $group => $data) {
		foreach ($data as $key => $values) {
			if ($key == $exchange) {
				return $values;
			}
		}
	}
	if ($throw_exception_on_failure) {
		throw new Exception("Could not find any exchange '$exchange'");
	} else {
		return false;
	}
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
			'primecoin' => '<a href="http://xpm.cryptocoinexplorer.com/">CryptoCoin explorer</a> (XPM)',
			'primecoin_block' => '<a href="http://xpm.cryptocoinexplorer.com/">CryptoCoin explorer</a> (XPM block count)',
			'terracoin' => '<a href="http://trc.cryptocoinexplorer.com/">CryptoCoin explorer</a> (TRC)',
			'terracoin_block' => '<a href="http://trc.cryptocoinexplorer.com/">CryptoCoin explorer</a> (TRC block count)',
			'dogecoin' => '<a href="http://dogechain.info/">DogeChain</a>',
			'dogecoin_block' => '<a href="http://dogechain.info/">DogeChain</a>',
			'megacoin' => '<a href="http://mega.rapta.net:2750/chain/Megacoin">Megacoin Block Explorer</a>',
			'megacoin_block' => '<a href="http://mega.rapta.net:2750/chain/Megacoin">Megacoin Block Explorer</a> (block count)',
			'ripple' => '<a href="http://ripple.com">Ripple</a>',
			'namecoin' => '<a href="http://192.241.222.65/chain/Namecoin">Namecoin</a>',
			'namecoin_block' => '<a href="http://192.241.222.65/chain/Namecoin">Namecoin</a> (block count)',
			'digitalcoin' => '<a href="http://dgc.cryptocoinexplorer.com/">CryptoCoin explorer</a> (DGC)',
			'digitalcoin_block' => '<a href="http://dgc,cryptocoinexplorer.com/">CryptoCoin explorer</a> (DGC block count)',
			'worldcoin' => '<a href="http://wdc.cryptocoinexplorer.com/">CryptoCoin explorer</a> (WDC)',
			'worldcoin_block' => '<a href="http://wdc,cryptocoinexplorer.com/">CryptoCoin explorer</a> (WDC block count)',
			'ixcoin' => '<a href="http://block.al.tcoin.info/chain/Ixcoin">Altcoin explorer</a> (IXC)',
			'ixcoin_block' => '<a href="http://block.al.tcoin.info/chain/Ixcoin">Altcoin explorer</a> (IXC block count)',
			'vertcoin' => '<a href="http://vertexplorer.com/">VERTexplorer</a>',
			'netcoin' => '<a href="http://explorer.netcoinfoundation.org/">Netcoin Explorer</a>',
			'netcoin_block' => '<a href="http://explorer.netcoinfoundation.org/">Netcoin Explorer</a> (block count)',
			'hobonickels' => '<a href="http://162.217.249.198:1080/chain/Hobonickels">Hobonickels</a>',
			'hobonickels_block' => '<a href="http://162.217.249.198:1080/chain/Hobonickels">Hobonickels</a> (block count)',
		),

		"Mining pool wallets" => array(
			'50btc' => '<a href="https://50btc.com/">50BTC</a>',
			'beeeeer' => '<a href="http://beeeeer.org/">' . htmlspecialchars(get_exchange_name('beeeeer')) . '</a>',
			'bitminter' => '<a href="https://bitminter.com/">BitMinter</a>',
			'btcguild' => '<a href="https://www.btcguild.com">BTC Guild</a>',
			'coinhuntr' => '<a href="https://coinhuntr.com/">CoinHuntr</a>',
			'cryptopools_dgc' => '<a href="http://dgc.cryptopools.com/">CryptoPools</a> (DGC)',
			'cryptotroll_doge' => '<a href="http://doge.cryptotroll.com">Cryptotroll</a> (DOGE)',
			'd2_wdc' => '<a href="https://wdc.d2.cc/">d2</a> (WDC)',
			'dedicatedpool_doge' => '<a href="http://doge.dedicatedpool.com">dedicatedpool.com</a> (DOGE)',
			'dogechainpool' => '<a href="http://pool.dogechain.info/">Dogechain Pool</a>',
			'dogepoolpw' => '<a href="http://dogepool.pw">dogepool.pw</a>',
			'ecoining_ppc' => '<a href="https://peercoin.ecoining.com/">Ecoining Peercoin</a>',
			'eligius' => '<a href="http://eligius.st/">Eligius</a>',
			'elitistjerks' => '<a href="https://www.ejpool.info/">Elitist Jerks</a>',
			'ghashio' => '<a href="https://ghash.io">GHash.io</a>',
			'givemecoins' => '<a href="https://www.give-me-coins.com">Give Me Coins</a>',
			'hashfaster_doge' => '<a href="http://doge.hashfaster.com">HashFaster</a> (DOGE)',
			'hashfaster_ftc' => '<a href="http://ftc.hashfaster.com">HashFaster</a> (FTC)',
			'hashfaster_ltc' => '<a href="http://ltc.hashfaster.com">HashFaster</a> (LTC)',
			'hypernova' => '<a href="https://hypernova.pw/">Hypernova</a>',
			'kattare' => '<a href="http://ltc.kattare.com/">ltc.kattare.com</a>',
			'khore' => '<a href="https://nvc.khore.org/">nvc.khore.org</a>',
			'lite_coinpool' => '<a href="http://lite.coin-pool.com/">lite.coin-pool.com</a>',
			'liteguardian' => '<a href="https://www.liteguardian.com/">LiteGuardian</a>',
			'litepooleu' => '<a href="http://litepool.eu/">Litepool</a>',
			'ltcmineru' => '<a href="http://ltcmine.ru/">LTCMine.ru</a>',
			'miningforeman' => '<a href="http://www.mining-foreman.org/">Mining Foreman</a> (LTC)',
			'miningforeman_ftc' => '<a href="http://ftc.mining-foreman.org/">Mining Foreman</a> (FTC)',
			'miningpoolco' => '<a href="https://www.miningpool.co/">MiningPool.co</a>',
			'multipool' => '<a href="https://multipool.us/">Multipool</a>',
			'nut2pools_ftc' => '<a href="https://ftc.nut2pools.com/">Nut2Pools</a> (FTC)',
			'ozcoin_btc' => '<a href="http://ozco.in/">Ozcoin</a> (BTC)',
			'ozcoin_ltc' => '<a href="https://lc.ozcoin.net/">Ozcoin</a> (LTC)',
			'poolx' => '<a href="http://pool-x.eu">Pool-x.eu</a>',
			'rapidhash_doge' => '<a href="https://doge.rapidhash.net/">RapidHash</a> (DOGE)',
			'rapidhash_vtc' => '<a href="https://vtc.rapidhash.net/">RapidHash</a> (VTC)',
			'scryptguild' => '<a href="https://www.scryptguild.com/">ScryptGuild</a>',
			'scryptpools' => '<a href="http://doge.scryptpools.com">scryptpools.com</a>',
			'securities_update_eligius' => '<a href="http://eligius.st/">Eligius</a> balances',
			'shibepool' => '<a href="http://shibepool.com/">Shibe Pool</a>',
			'slush' => '<a href="https://mining.bitcoin.cz">Slush\'s pool</a>',
			'teamdoge' => '<a href="https://teamdoge.com/">TeamDoge</a>',
			'triplemining' => '<a href="https://www.triplemining.com/">TripleMining</a>',
			'wemineftc' => '<a href="https://www.wemineftc.com">WeMineFTC</a>',
			'wemineltc' => '<a href="https://www.wemineltc.com">WeMineLTC</a>',
			'ypool' => '<a href="http://ypool.net">ypool.net</a>',
		),

		"Exchange wallets" => array(
			'bips' => '<a href="https://bips.me">BIPS</a>',
			'bit2c' => '<a href="https://www.bit2c.co.il">Bit2c</a>',
			'bitcurex_eur' => '<a href="https://eur.bitcurex.com/">Bitcurex EUR</a>',
			'bitcurex_pln' => '<a href="https://pln.bitcurex.com/">Bitcurex PLN</a>',
			'bitmarket_pl' => '<a href="https://www.bitmarket.pl">BitMarket.pl</a>',
			'bitstamp' => '<a href="https://www.bitstamp.net">Bitstamp</a>',
			'btce' => '<a href="http://btc-e.com">BTC-e</a>',
			'btcinve' => '<a href="https://btcinve.com">BTCInve</a>',
			'cexio' => '<a href="https://cex.io">CEX.io</a>',
			'coinbase' => '<a href="https://coinbase.com">Coinbase</a>',
			'crypto-trade' => '<a href="https://www.crypto-trade.com">Crypto-Trade</a>',
			'cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a>',
			'cryptsy' => '<a href="https://www.cryptsy.com/">Crypsty</a>',
			'justcoin' => '<a href="https://justcoin.com/">Justcoin</a>',
			'kraken' => '<a href="https://www.kraken.com/">Kraken</a>',
			'havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a>',
			'litecoininvest' => '<a href="https://litecoininvest.com">Litecoininvest</a>',
			'mtgox' => '<a href="http://mtgox.com">Mt.Gox</a>',
			'poloniex' => '<a href="https://www.poloniex.com">Poloniex</a>',
			'vaultofsatoshi' => '<a href="https://www.vaultofsatoshi.com">Vault of Satoshi</a>',
			'vircurex' => '<a href="https://vircurex.com">Vircurex</a>',
		),

		"Exchange tickers" => array(
			'ticker_bitnz' => '<a href="http://bitnz.com">BitNZ</a>',
			'ticker_bitcurex' => '<a href="https://bitcurex.com/">Bitcurex</a>',
			'ticker_bitmarket_pl' => '<a href="https://www.bitmarket.pl/">BitMarket.pl</a>',
			'ticker_bitstamp' => '<a href="https://www.bitstamp.net/">Bitstamp</a>',
			'ticker_btcchina' => '<a href="https://btcchina.com">BTC China</a>',
			'ticker_btce' => '<a href="http://btc-e.com">BTC-e</a>',
			'ticker_cexio' => '<a href="https://cex.io">CEX.io</a>',
			'ticker_coins-e' => '<a href="https://www.coins-e.com">Coins-E</a>',
			'ticker_justcoin' => '<a href="https://justcoin.com/">Justcoin</a>',
			'ticker_kraken' => '<a href="https://www.kraken.com/">Kraken</a>',
			'ticker_mintpal' => '<a href="https://www.mintpal.com/">MintPal</a>',
			'ticker_mtgox' => '<a href="http://mtgox.com">Mt.Gox</a>',
			'ticker_poloniex' => '<a href="https://www.poloniex.com">Poloniex</a>',
			'ticker_themoneyconverter' => '<a href="http://themoneyconverter.com">TheMoneyConverter</a>',
			'ticker_vircurex' => '<a href="https://vircurex.com">Vircurex</a>',
			'ticker_virtex' => '<a href="https://www.cavirtex.com/">VirtEx</a>',
		),

		"Security exchanges" => array(
			'securities_796' => '<a href="https://796.com">796 Xchange</a>',
			'ticker_crypto-trade' => '<a href="https://crypto-trade.com">Crypto-Trade</a>',		// securities for crypto-trade are handled by the ticker_crypto-trade
			'securities_cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a>',
			'securities_havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a>',
			'securities_update_btcinve' => '<a href="https://btcinve.com">BTCInve</a> Securities list',
			'securities_update_cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a> Securities list',
			'securities_update_havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a> Securities list',
			'securities_update_litecoininvest' => '<a href="https://litecoininvest.com">Litecoininvest</a> Securities list',
		),

		"Individual securities" => array(
			'individual_crypto-trade' => '<a href="https://crypto-trade.com">Crypto-Trade</a>',
			'individual_cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a>',
			'individual_havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a>',
			'individual_litecoininvest' => '<a href="https://litecoininvest.com">Litecoininvest</a>',
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
			return " " . t("wallet");

		case "Exchange tickers":
			return " " . t("ticker");

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
				'client' => get_currency_name('ftc'),
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
				'client' => get_currency_name('ppc'),
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
				'client' => get_currency_name('nvc'),
			);

		case "xpm":
			return array(
				'premium_group' => 'primecoin',
				'title' => 'XPM address',
				'titles' => 'XPM addresses',
				'table' => 'addresses',
				'currency' => 'xpm',
				'callback' => 'is_valid_xpm_address',
				'job_type' => 'primecoin',
				'client' => get_currency_name('xpm'),
			);

		case "trc":
			return array(
				'premium_group' => 'terracoin',
				'title' => 'TRC address',
				'titles' => 'TRC addresses',
				'table' => 'addresses',
				'currency' => 'trc',
				'callback' => 'is_valid_trc_address',
				'job_type' => 'terracoin',
				'client' => get_currency_name('trc'),
			);

		case "dog":
			return array(
				'premium_group' => 'dogecoin',
				'title' => 'DOGE address',
				'titles' => 'DOGE addresses',
				'table' => 'addresses',
				'currency' => 'dog',
				'callback' => 'is_valid_dog_address',
				'job_type' => 'dogecoin',
				'client' => get_currency_name('dog'),
			);

		case "mec":
			return array(
				'premium_group' => 'megacoin',
				'title' => 'MEC address',
				'titles' => 'MEC addresses',
				'table' => 'addresses',
				'currency' => 'mec',
				'callback' => 'is_valid_mec_address',
				'job_type' => 'megacoin',
				'client' => get_currency_name('mec'),
			);

		case "xrp":
			return array(
				'premium_group' => 'ripple',
				'title' => 'XRP address',
				'titles' => 'XRP addresses',
				'table' => 'addresses',
				'currency' => 'xrp',
				'callback' => 'is_valid_xrp_address',
				'job_type' => 'ripple',
				'client' => get_currency_name('xrp'),
			);

		case "nmc":
			return array(
				'premium_group' => 'namecoin',
				'title' => 'NMC address',
				'titles' => 'NMC addresses',
				'table' => 'addresses',
				'currency' => 'nmc',
				'callback' => 'is_valid_nmc_address',
				'job_type' => 'namecoin',
				'client' => get_currency_name('nmc'),
			);

		case "dgc":
			return array(
				'premium_group' => 'digitalcoin',
				'title' => 'DGC address',
				'titles' => 'DGC addresses',
				'table' => 'addresses',
				'currency' => 'dgc',
				'callback' => 'is_valid_dgc_address',
				'job_type' => 'digitalcoin',
				'client' => get_currency_name('dgc'),
			);

		case "wdc":
			return array(
				'premium_group' => 'worldcoin',
				'title' => 'WDC address',
				'titles' => 'WDC addresses',
				'table' => 'addresses',
				'currency' => 'wdc',
				'callback' => 'is_valid_wdc_address',
				'job_type' => 'worldcoin',
				'client' => get_currency_name('wdc'),
			);

		case "ixc":
			return array(
				'premium_group' => 'ixcoin',
				'title' => 'IXC address',
				'titles' => 'IXC addresses',
				'table' => 'addresses',
				'currency' => 'ixc',
				'callback' => 'is_valid_ixc_address',
				'job_type' => 'ixcoin',
				'client' => get_currency_name('ixc'),
			);

		case "vtc":
			return array(
				'premium_group' => 'vertcoin',
				'title' => 'VTC address',
				'titles' => 'VTC addresses',
				'table' => 'addresses',
				'currency' => 'vtc',
				'callback' => 'is_valid_vtc_address',
				'job_type' => 'vertcoin',
				'client' => get_currency_name('vtc'),
			);

		case "net":
			return array(
				'premium_group' => 'netcoin',
				'title' => 'NET address',
				'titles' => 'NET addresses',
				'table' => 'addresses',
				'currency' => 'net',
				'callback' => 'is_valid_net_address',
				'job_type' => 'netcoin',
				'client' => get_currency_name('net'),
			);

		case "hbn":
			return array(
				'premium_group' => 'hobonickels',
				'title' => 'HBN address',
				'titles' => 'HBN addresses',
				'table' => 'addresses',
				'currency' => 'hbn',
				'callback' => 'is_valid_hbn_address',
				'job_type' => 'hobonickels',
				'client' => get_currency_name('hbn'),
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
	foreach ($result['inputs'] as $key => $data) {
		$result['inputs'][$key]['key'] = $key;
	}
	foreach (account_data_grouped() as $group => $data) {
		foreach ($data as $key => $values) {
			if ($key == $exchange && isset($values['wizard'])) {
				$result['wizard'] = $values['wizard'];
			}
		}
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

		case "wemineftc":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mmcfe_apikey'),
				),
				'table' => 'accounts_wemineftc',
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

		case "kattare":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_kattare_apikey'),
				),
				'table' => 'accounts_kattare',
				'khash' => true,
			);

		case "litepooleu":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_litepooleu_apikey'),
				),
				'table' => 'accounts_litepooleu',
				'khash' => true,
			);

		case "coinhuntr":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_coinhuntr_apikey'),
				),
				'table' => 'accounts_coinhuntr',
				'khash' => true,
			);

		case "eligius":
			return array(
				'inputs' => array(
					'btc_address' => array('title' => 'BTC Address', 'callback' => 'is_valid_btc_address'),
				),
				'table' => 'accounts_eligius',
			);

		case "lite_coinpool":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_lite_coinpool_apikey'),
				),
				'table' => 'accounts_lite_coinpool',
				'khash' => true,
			);

		case "beeeeer":
			return array(
				'inputs' => array(
					'xpm_address' => array('title' => 'XPM Address', 'callback' => 'is_valid_xpm_address'),
				),
				'table' => 'accounts_beeeeer',
			);

		case "litecoinpool":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_litecoinpool_apikey'),
				),
				'table' => 'accounts_litecoinpool',
				'khash' => true,
			);

		case "dogepoolpw":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_dogepoolpw_apikey'),
				),
				'table' => 'accounts_dogepoolpw',
				'khash' => true,
			);

		case "elitistjerks":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_elitistjerks_apikey'),
				),
				'table' => 'accounts_elitistjerks',
				'khash' => true,
			);

		case "dogechainpool":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_dogechainpool_apikey'),
				),
				'table' => 'accounts_dogechainpool',
				'khash' => true,
			);

		case "hashfaster_ltc":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
				),
				'table' => 'accounts_hashfaster_ltc',
				'title' => 'HashFaster LTC account',
				'khash' => true,
				'title_key' => 'hashfaster',
			);

		case "hashfaster_ftc":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
				),
				'table' => 'accounts_hashfaster_ftc',
				'title' => 'HashFaster FTC account',
				'khash' => true,
				'title_key' => 'hashfaster',
			);

		case "hashfaster_doge":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
				),
				'table' => 'accounts_hashfaster_doge',
				'title' => 'HashFaster DOGE account',
				'khash' => true,
				'title_key' => 'hashfaster',
			);

		case "triplemining":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_triplemining_apikey'),
				),
				'table' => 'accounts_triplemining',
			);

		case "ozcoin_ltc":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_ozcoin_ltc_apikey'),
				),
				'table' => 'accounts_ozcoin_ltc',
				'title' => 'Ozcoin LTC account',
				'khash' => true,
				'title_key' => 'ozcoin',
			);

		case "ozcoin_btc":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_ozcoin_btc_apikey'),
				),
				'table' => 'accounts_ozcoin_btc',
				'title' => 'Ozcoin BTC account',
				'title_key' => 'ozcoin',
			);

		case "scryptpools":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
				),
				'table' => 'accounts_scryptpools',
				'khash' => true,
			);

		case "multipool":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_multipool_apikey'),
				),
				'table' => 'accounts_multipool',
				'khash' => true,		// it's actually both MH/s (BTC) and KH/s (LTC) but we will assume KH/s is more common
			);

		case "ypool":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_ypool_apikey'),
				),
				'table' => 'accounts_ypool',
				'khash' => true,
			);

		case "miningpoolco":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_miningpoolco_apikey'),
				),
				'table' => 'accounts_miningpoolco',
				'khash' => true,
			);

		case "smalltimeminer_mec":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
				),
				'table' => 'accounts_smalltimeminer_mec',
				'title' => 'Small Time Miner Megacoin account',
				'khash' => true,
				'title_key' => 'smalltimeminer',
			);

		case "ecoining_ppc":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
				),
				'table' => 'accounts_ecoining_ppc',
				'title' => 'Ecoining Peercoin account',
				'title_key' => 'ecoining',
			);

		case "teamdoge":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
				),
				'table' => 'accounts_teamdoge',
				'khash' => true,
			);

		case "dedicatedpool_doge":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
				),
				'table' => 'accounts_dedicatedpool_doge',
				'title' => 'dedicatedpool.com DOGE account',
				'title_key' => 'dedicatedpool',
			);

		case "nut2pools_ftc":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
				),
				'table' => 'accounts_nut2pools_ftc',
				'title' => 'Nut2Pools FTC account',
				'title_key' => 'nut2pools',
			);

		case "shibepool":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
				),
				'table' => 'accounts_shibepool',
				'khash' => true,
			);

		case "cryptopools_dgc":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
				),
				'table' => 'accounts_cryptopools_dgc',
				'title' => 'CryptoPools DGC account',
				'khash' => true,
			);

		case "d2_wdc":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mmcfe_apikey'),
				),
				'table' => 'accounts_d2_wdc',
				'title' => 'd2 DOGE account',
				'khash' => true,
				'title_key' => 'd2',
			);

		case "scryptguild":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_scryptguild_apikey'),
				),
				'table' => 'accounts_scryptguild',
				'khash' => true,
			);

		case "rapidhash_doge":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
				),
				'table' => 'accounts_rapidhash_doge',
				'title' => 'RapidHash DOGE account',
				'title_key' => 'rapidhash',
			);

		case "rapidhash_vtc":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
				),
				'table' => 'accounts_rapidhash_vtc',
				'title' => 'RapidHash VTC account',
				'title_key' => 'rapidhash',
			);

		case "cryptotroll_doge":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
				),
				'table' => 'accounts_cryptotroll_doge',
				'title' => 'Cryptotroll DOGE account',
				'title_key' => 'cryptotroll',
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

		case "bit2c":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_bit2c_apikey'),
					'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_bit2c_apisecret', 'length' => 128),
				),
				'table' => 'accounts_bit2c',
				'title' => 'Bit2c account',
			);

		case "bitcurex_pln":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_bitcurex_pln_apikey'),
					'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_bitcurex_pln_apisecret', 'length' => 128),
				),
				'table' => 'accounts_bitcurex_pln',
			);

		case "bitcurex_eur":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_bitcurex_eur_apikey'),
					'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_bitcurex_eur_apisecret', 'length' => 128),
				),
				'table' => 'accounts_bitcurex_eur',
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

		case "ghashio":
			return array(
				'inputs' => array(
					'api_username' => array('title' => 'Username', 'callback' => 'is_valid_cexio_apiusername'),
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_cexio_apikey'),
					'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_cexio_apisecret', 'length' => 32),
				),
				'table' => 'accounts_ghashio',
			);

		case "crypto-trade":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_cryptotrade_apikey'),
					'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_cryptotrade_apisecret'),
				),
				'table' => 'accounts_cryptotrade',
			);

		case "bitstamp":
			return array(
				'inputs' => array(
					'api_client_id' => array('title' => 'Customer ID', 'callback' => 'is_numeric'),
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_bitstamp_apikey'),
					'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_bitstamp_apisecret', 'length' => 32),
				),
				'table' => 'accounts_bitstamp',
			);

		case "justcoin":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_justcoin_apikey'),
				),
				'table' => 'accounts_justcoin',
			);

		case "cryptsy":
			return array(
				'inputs' => array(
					'api_public_key' => array('title' => 'Application key', 'callback' => 'is_valid_cryptsy_public_key', 'length' => 40),
					'api_private_key' => array('title' => 'App ID', 'callback' => 'is_valid_cryptsy_private_key', 'length' => 80),
				),
				'table' => 'accounts_cryptsy',
			);

		case "coinbase":
			return array(
				'inputs' => array(
					// we don't expose api_code here; this is obtained through the OAuth2 callback
				),
				'table' => 'accounts_coinbase',
			);

		case "vaultofsatoshi":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_vaultofsatoshi_apikey'),
					'api_secret' => array('title' => 'API secret key', 'callback' => 'is_valid_vaultofsatoshi_apisecret'),
				),
				'table' => 'accounts_vaultofsatoshi',
			);

		case "kraken":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_kraken_apikey'),
					'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_kraken_apisecret', 'length' => 128),
				),
				'table' => 'accounts_kraken',
			);

		case "bitmarket_pl":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_bitmarket_pl_apikey'),
					'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_bitmarket_pl_apisecret', 'length' => 128),
				),
				'table' => 'accounts_bitmarket_pl',
			);

		case "poloniex":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_poloniex_apikey'),
					'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_poloniex_apisecret', 'length' => 128),
					'accept' => array('title' => 'I accept that this API is unsafe', 'checkbox' => true, 'callback' => 'number_format'),
				),
				'unsafe' => "A Poloniex API key allows trading, but does not allow withdrawl.",
				'table' => 'accounts_poloniex',
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

		case "796":
			return array(
				'inputs' => array(
					'api_app_id' => array('title' => 'Application ID', 'callback' => 'is_numeric'),
					'api_key' => array('title' => 'API Key', 'callback' => 'is_valid_796_apikey'),
					'api_secret' => array('title' => 'API Secret', 'callback' => 'is_valid_796_apisecret'),
				),
				'table' => 'accounts_796',
			);

		case "litecoininvest":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_litecoininvest_apikey'),
				),
				'table' => 'accounts_litecoininvest',
			);

		case "btcinve":
			return array(
				'inputs' => array(
					'api_key' => array('title' => 'API key', 'callback' => 'is_valid_litecoininvest_apikey'),
				),
				'table' => 'accounts_btcinve',
			);

		// --- securities ---
		case "individual_litecoinglobal":
			return array(
				'inputs' => array(
					'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
					'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_litecoinglobal_securities', 'callback' => 'is_valid_id'),
				),
				'table' => 'accounts_individual_litecoinglobal',
			);

		case "individual_btct":
			return array(
				'inputs' => array(
					'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
					'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_btct_securities', 'callback' => 'is_valid_id'),
				),
				'table' => 'accounts_individual_btct',
			);

		case "individual_bitfunder":
			return array(
				'inputs' => array(
					'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
					'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_bitfunder_securities', 'callback' => 'is_valid_id'),
				),
				'table' => 'accounts_individual_bitfunder',
			);

		case "individual_cryptostocks":
			return array(
				'inputs' => array(
					'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
					'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_cryptostocks_securities', 'callback' => 'is_valid_id'),
				),
				'table' => 'accounts_individual_cryptostocks',
			);

		case "individual_havelock":
			return array(
				'inputs' => array(
					'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
					'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_havelock_securities', 'callback' => 'is_valid_id'),
				),
				'table' => 'accounts_individual_havelock',
			);

		case "individual_crypto-trade":
			return array(
				'inputs' => array(
					'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
					'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_cryptotrade_securities', 'callback' => 'is_valid_id'),
				),
				'table' => 'accounts_individual_cryptotrade',
			);

		case "individual_796":
			return array(
				'inputs' => array(
					'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
					'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_796_securities', 'callback' => 'is_valid_id'),
				),
				'table' => 'accounts_individual_796',
			);

		case "individual_litecoininvest":
			return array(
				'inputs' => array(
					'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
					'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_litecoininvest_securities', 'callback' => 'is_valid_id'),
				),
				'table' => 'accounts_individual_litecoininvest',
			);

		case "individual_btcinve":
			return array(
				'inputs' => array(
					'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
					'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_btcinve_securities', 'callback' => 'is_valid_id'),
				),
				'table' => 'accounts_individual_btcinve',
			);

		// --- other ---
		case "generic":
			return array(
				'inputs' => array(
					'api_url' => array('title' => 'URL', 'callback' => 'is_valid_generic_url', 'length' => 255),
					'currency' => array('title' => t('Currency'), 'dropdown' => 'dropdown_currency_list', 'callback' => 'is_valid_currency', 'style_prefix' => 'currency_name_'),
					'multiplier' => array('title' => t('Multiplier'), 'callback' => 'is_numeric', 'length' => 6, 'default' => 1, 'number' => true),
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
		// this should only be used for transaction creators!
		case "addresses":
			$account_type = array(
				'title' => t('Address'),
				'titles' => t('Addresses'),
				'wizard' => 'addresses',
				'transaction_creation' => true,
			);
			break;

		// this should only be used for transaction creators!
		case "notifications":
			$account_type = array(
				'title' => t('Notification'),
				'titles' => t('Notifications'),
				'wizard' => 'notifications',
				'transaction_creation' => false,
			);
			break;

		case "exchanges":
			$account_type = array(
				'title' => t('Exchange'),
				'titles' => t('Exchanges'),
				'wizard' => 'exchanges',
				'hashrate' => false,
				'url' => 'wizard_accounts_exchanges',
				'add_help' => 'add_service',
				'a' => 'an',
				'transaction_creation' => true,
			);
			break;

		case "pools":
			$account_type = array(
				'title' => t('Mining Pool'),
				'titles' => t('Mining Pools'),
				'wizard' => 'pools',
				'hashrate' => true,
				'url' => 'wizard_accounts_pools',
				'add_help' => 'add_service',
				'transaction_creation' => true,
			);
			break;

		case "securities":
			$account_type = array(
				'title' => t('Securities Exchange'),
				'titles' => t('Securities Exchanges'),
				'wizard' => 'securities',
				'hashrate' => false,
				'url' => 'wizard_accounts_securities',
				'add_help' => 'add_service',
			);
			break;

		case "individual":
			$account_type = array(
				'title' => t('Individual Security'),
				'titles' => t('Individual Securities'),
				'accounts' => 'securities',
				'wizard' => 'individual',
				'hashrate' => false,
				'url' => 'wizard_accounts_individual_securities',
				'first_heading' => t('Exchange'),
				'display_headings' => array('security' => t('Security'), 'quantity' => t('Quantity')),
				'display_callback' => 'get_individual_security_config',
				'add_help' => 'add_service',
				'a' => 'an',
			);
			break;

		case "other":
			$account_type = array(
				'title' => t('Other Account'),
				'titles' => t('Other Accounts'),
				'wizard' => 'other',
				'hashrate' => false,
				'url' => 'wizard_accounts_other',
				'add_help' => 'add_service',
				'a' => 'an',
				'display_headings' => array('multiplier' => t('Multiplier')),
				'display_editable' => array('multiplier' => 'number_format_autoprecision'),
				'transaction_creation' => true,
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
	if (!isset($account_type['display_editable'])) {
		$account_type['display_editable'] = array();
	}
	if (!isset($account_type['first_heading'])) {
		$account_type['first_heading'] = $account_type['title'];
	}
	if (!isset($account_type['accounts'])) {
		$account_type['accounts'] = "accounts";
	}
	if (!isset($account_type['a'])) {
		$account_type['a'] = "a";
	}
	if (!isset($account_type['transaction_creation'])) {
		$account_type['transaction_creation'] = false;
	}

	return $account_type;
}

function get_individual_security_config($account) {
	$security = "(unknown exchange)";
	$securities = false;
	$historical_key = false;		// used to link from wizard_accounts_individual_securities to historical
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
		case "individual_796":
			$securities = dropdown_get_796_securities();
			$historical_key = 'securities_796_btc';
			break;
		case "individual_litecoininvest":
			$securities = dropdown_get_litecoininvest_securities();
			$historical_key = 'securities_litecoininvest_ltc';
			break;
		case "individual_btcinve":
			$securities = dropdown_get_btcinve_securities();
			$historical_key = 'securities_btcinve_btc';
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
		'verisign' => array('Symantec PIP', 'https://pip.verisignlabs.com/'),
		'launchpad' => array('Launchpad', 'https://login.launchpad.net/'),
		'aol' => array('AOL', 'https://openid.aol.com/'),
	);
}

/**
 * A helper function to match (OpenID URLs) to default OpenID providers.
 * Each URL is matched as a regexp.
 */
function get_openid_provider_formats() {
	return array(
		'#^https?://www.google.com/accounts/#im' => 'google',
		// '#^https?://profiles.google.com/#im' => 'google-plus',
		'#^https?://openid.stackexchange.com/#im' => 'stackexchange',
		'#^https?://openid.aol.com/#im' => 'aol',
		'#^https?://me.yahoo.com/#im' => 'yahoo',
		// '#^https?://[^\\.]+.myopenid.com/#im' => 'myopenid',
		'#^https?://[^\\.]+.verisignlabs.com/#im' => 'verisign',
		// '#^https?://[^\\.]+.wordpress.com/#im' => 'wordpress',
		'#^https?://[^\\.]+.blogspot.com/#im' => 'blogspot',
		'#^https?://launchpad.net/~#im' => 'launchpad',
		'#^https?://login.launchpad.net/\\+#im' => 'launchpad',
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

function get_permitted_notification_periods() {
	return array(
		'hour' => array('label' => t('the last hour'), 'title' => 'hourly', 'interval' => 'INTERVAL 1 HOUR'),
		'day' => array('label' => t('the last day'), 'title' => 'daily', 'interval' => 'INTERVAL 1 DAY'),
		'week' => array('label' => t('the last week'), 'title' => 'weekly', 'interval' => 'INTERVAL 1 WEEK'),
		'month' => array('label' => t('the last month'), 'title' => 'monthly', 'interval' => 'INTERVAL 1 MONTH'),
	);
}

function get_permitted_notification_conditions() {
	return array(
		'increases_by' => t("increases by"),
		'increases' => t("increases"),
		'above' => t("is above"),
		'decreases_by' => t("decreases by"),
		'decreases' => t("decreases"),
		'below' => t("is below"),
	);
}

function get_permitted_deltas() {
	$permitted_days = array(
		'' => array('title' => 'value', 'description' => t('None')),
		'absolute' => array('title' => 'change', 'description' => t('Change')),
		'percent' => array('title' => 'percent', 'description' => t('% change')),
	);
	return $permitted_days;
}

$_latest_tickers = array();
/**
 * Get the latest ticker value for the given exchange and currency pairs.
 * Allows for caching these values.
 * @returns false if no ticker value could be found.
 */
function get_latest_ticker($exchange, $cur1, $cur2) {
	$key = $exchange . '_' . $cur1 . '_' . $cur2;
	global $_latest_tickers;
	if (!isset($_latest_tickers[$key])) {
		$latest_tickers[$key] = false;
		$q = db()->prepare("SELECT * FROM ticker_recent WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 LIMIT 1");
		$q->execute(array(
			"exchange" => $exchange,
			"currency1" => $cur1,
			"currency2" => $cur2,
		));
		if ($ticker = $q->fetch()) {
			set_latest_ticker($ticker);
		}
	}
	return $_latest_tickers[$key];
}
// used for testing
function set_latest_ticker($ticker) {
	$exchange = $ticker['exchange'];
	$cur1 = $ticker['currency1'];
	$cur2 = $ticker['currency2'];
	$key = $exchange . '_' . $cur1 . '_' . $cur2;
	global $_latest_tickers;
	$_latest_tickers[$key] = $ticker;
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
	$q->execute(array($user_id, 'summary_usd_bitstamp'));

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
		$result[$c] = get_currency_abbr($c);
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

function dropdown_get_796_securities() {
	return dropdown_get_all_securities('securities_796', 'title');
}

function dropdown_get_litecoininvest_securities() {
	return dropdown_get_all_securities('securities_litecoininvest');
}

function dropdown_get_btcinve_securities() {
	return dropdown_get_all_securities('securities_btcinve');
}

/**
 * Returns an array of (id => security name).
 * Cached across calls.
 */
$dropdown_get_all_securities = array();
function dropdown_get_all_securities($table, $title_key = 'name') {
	global $dropdown_get_all_securities;
	if (!isset($dropdown_get_all_securities[$table])) {
		$dropdown_get_all_securities[$table] = array();
		$q = db()->prepare("SELECT id, $title_key AS name FROM " . $table);
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

function is_valid_xpm_address($address) {
	// based on is_valid_btc_address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "A")
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}

function is_valid_dog_address($address) {
	// based on is_valid_btc_address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "D")
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}

function is_valid_mec_address($address) {
	// based on is_valid_btc_address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "M")
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}

function is_valid_xrp_address($address) {
	// based on is_valid_btc_address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "r")
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}

function is_valid_nmc_address($address) {
	// based on is_valid_btc_address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "M" || substr($address, 0, 1) == "N")
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}

function is_valid_dgc_address($address) {
	// based on is_valid_btc_address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "D")
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}

function is_valid_wdc_address($address) {
	// based on is_valid_btc_address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "W")
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}

function is_valid_ixc_address($address) {
	// based on is_valid_btc_address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "x")
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}

function is_valid_vtc_address($address) {
	// based on is_valid_btc_address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "V")
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}

function is_valid_net_address($address) {
	// based on is_valid_btc_address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "n")
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}

function is_valid_hbn_address($address) {
	// based on is_valid_btc_address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "E" || substr($address, 0, 1) == "F")
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}

function is_valid_trc_address($address) {
	// based on is_valid_btc_address
	return is_valid_btc_address($address);
}

function is_valid_mmcfe_apikey($key) {
	// not sure what the format should be, seems to be 64 character hexadecmial
	return strlen($key) == 64 && preg_match("#^[a-z0-9]+$#", $key);
}

function is_valid_bit2c_apikey($key) {
	// not sure what the format should be
	return preg_match("#^[a-z0-9]+-[a-z0-9]+-[a-z0-9]+-[a-z0-9]+-[a-z0-9]+$#", $key);
}

function is_valid_bit2c_apisecret($key) {
	// not sure what the format should be
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
	// looks like a number followed by a 32 character hex string
	return strlen($key) >= 33 && preg_match("#^[0-9]+\-[a-f0-9]+$#", $key);
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

function is_valid_kattare_apikey($key) {
	// looks like a 64 character hex string
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_cexio_apikey($key) {
	// looks like a 20-32 character alphanumeric mixed case string
	return strlen($key) >= 20 && strlen($key) <= 32 && preg_match("#^[A-Za-z0-9]+$#", $key);
}

function is_valid_cexio_apisecret($key) {
	// looks like a 20-32 character alphanumeric mixed case string
	return strlen($key) >= 20 && strlen($key) <= 32 && preg_match("#^[A-Za-z0-9]+$#", $key);
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

function is_valid_bitstamp_apikey($key) {
	// looks like a 32 character alphanumeric string
	return strlen($key) == 32 && preg_match("#^[A-Za-z0-9]+$#", $key);
}

function is_valid_bitstamp_apisecret($key) {
	// looks like a 32 character alphanumeric string
	return strlen($key) == 32 && preg_match("#^[A-Za-z0-9]+$#", $key);
}

function is_valid_796_apikey($key) {
	// guessing the format
	return preg_match("#^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{8}$#", $key);
}

function is_valid_796_apisecret($key) {
	// looks like a 60 character crazy string
	return strlen($key) == 60 && preg_match("#^[A-Za-z0-9\\+\\/]+$#", $key);
}

function is_valid_litepooleu_apikey($key) {
	// looks like a 64 character hex string
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_coinhuntr_apikey($key) {
	// looks like a 64 character hex string
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_lite_coinpool_apikey($key) {
	// looks like a 64 character hex string
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_litecoinpool_apikey($key) {
	// looks like a 32 character hex string
	return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_dogepoolpw_apikey($key) {
	// looks like a 64 character hex string
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_elitistjerks_apikey($key) {
	// looks like a 64 character hex string
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_dogechainpool_apikey($key) {
	// looks like a 64 character hex string
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_triplemining_apikey($key) {
	// looks like a 32 character hex string
	return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_ozcoin_ltc_apikey($key) {
	// guessing the format
	return preg_match("#^[0-9]+_[a-zA-Z]+$#", $key);
}

function is_valid_ozcoin_btc_apikey($key) {
	// guessing the format
	return preg_match("#^[0-9]+_[a-zA-Z]+$#", $key);
}

function is_valid_bitcurex_pln_apikey($key) {
	// looks like a 64 character hex string
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_bitcurex_pln_apisecret($key) {
	// looks like a long base64 encoded string
	return strlen($key) > 60 && strlen($key) < 100 && preg_match("#^[a-zA-Z0-9/\\+=]+$#", $key);
}

function is_valid_bitcurex_eur_apikey($key) {
	return is_valid_bitcurex_pln_apikey($key);
}

function is_valid_bitcurex_eur_apisecret($key) {
	return is_valid_bitcurex_pln_apisecret($key);
}

function is_valid_justcoin_apikey($key) {
	// looks like a 64 character hex string
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_multipool_apikey($key) {
	// looks like a 64 character hex string
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_ypool_apikey($key) {
	// looks like a 20 character string of almost any characters
	return strlen(trim($key)) == 20;
}

function is_valid_cryptsy_public_key($key) {
	// looks like a 40 character hex string (full trade) or 18-19 characters (application keys)
	return (strlen($key) >= 16 || strlen($key) <= 40) && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_cryptsy_private_key($key) {
	// can be anything
	return strlen($key) > 0;
}

function is_valid_litecoininvest_apikey($key) {
	// looks to be lowercase hex
	return preg_match("#^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$#", $key);
}

function is_valid_miningpoolco_apikey($key) {
	// looks like a 40 character hex string
	return strlen($key) == 40 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_vaultofsatoshi_apikey($key) {
	// looks like a 64 character alphanumeric string
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_vaultofsatoshi_apisecret($key) {
	// looks like a 64 character alphanumeric string
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_mpos_apikey($key) {
	// looks like a 64 character hex string
	return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_scryptguild_apikey($key) {
	// looks like a 32 character hex string
	return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_kraken_apikey($key) {
	return strlen($key) == 56 && preg_match("#^[a-zA-Z0-9=/+]+$#", $key);
}

function is_valid_kraken_apisecret($key) {
	return strlen($key) > 64 && strlen($key) < 128 && preg_match("#^[a-zA-Z0-9=/+]+$#", $key);
}

function is_valid_bitmarket_pl_apikey($key) {
	// looks like a 32 character hex string
	return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_bitmarket_pl_apisecret($key) {
	// looks like a 32 character hex string
	return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_poloniex_apikey($key) {
	// looks like 4 sets of 8 characters
	return preg_match("#^[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}+$#", $key);
}

function is_valid_poloniex_apisecret($key) {
	// looks like a 128 character hex string
	return strlen($key) == 128 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_currency($c) {
	return in_array($c, get_all_currencies());
}

function is_valid_generic_url($url) {
	return preg_match("#^https?://.+$#imu", $url) && strlen($url) <= 255;
}

function is_valid_name($s) {
	return mb_strlen($s) < 64;
}

function is_valid_title($s) {
	return mb_strlen($s) < 64;
}

function is_valid_quantity($n) {
	return is_numeric($n) && $n == (int) $n && $n > 0;
}

function is_valid_id($n) {
	return is_numeric($n) && $n == (int) $n && $n > 0;
}

