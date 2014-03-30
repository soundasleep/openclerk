<?php

/**
 * Ticker job (any exchange) - delegates out to jobs/ticker/<exchange>
 */

// get the relevant summary
$q = db()->prepare("SELECT * FROM exchanges WHERE id=?");
$q->execute(array($job['arg_id']));
$exchange = $q->fetch();
if (!$exchange) {
	throw new JobException("Cannot find an exchange " . $job['arg_id']);
}
$job['arg0'] = $exchange['name'];		// issue #135: for performance metrics later

// what kind of exchange is it?
// each exchange will insert in many different currency pairs, depending on how many
// currencies are supported
switch ($exchange['name']) {
	case "btce":
		require(__DIR__ . "/ticker/btce.php");
		break;

	case "bitnz":
		require(__DIR__ . "/ticker/bitnz.php");
		break;

	case "mtgox":
		require(__DIR__ . "/ticker/mtgox.php");
		break;

	case "vircurex":
		require(__DIR__ . "/ticker/vircurex.php");
		break;

	case "themoneyconverter":
		require(__DIR__ . "/ticker/themoneyconverter.php");
		break;

	case "virtex":
		require(__DIR__ . "/ticker/virtex.php");
		break;

	case "bitstamp":
		require(__DIR__ . "/ticker/bitstamp.php");
		break;

	case "cexio":
		require(__DIR__ . "/ticker/cexio.php");
		break;

	case "crypto-trade":
		require(__DIR__ . "/ticker/crypto-trade.php");
		break;

	case "btcchina":
		require(__DIR__ . "/ticker/btcchina.php");
		break;

	case "cryptsy":
		require(__DIR__ . "/ticker/cryptsy.php");
		break;

	case "coins-e":
		require(__DIR__ . "/ticker/coins-e.php");
		break;

	case "bitcurex":
		require(__DIR__ . "/ticker/bitcurex.php");
		break;

	case "justcoin":
		require(__DIR__ . "/ticker/justcoin.php");
		break;

	case "coinbase":
		require(__DIR__ . "/ticker/coinbase.php");
		break;

	case "vaultofsatoshi":
		require(__DIR__ . "/ticker/vaultofsatoshi.php");
		break;

	default:
		throw new JobException("Unknown exchange " . $exchange['name']);
		break;
}
