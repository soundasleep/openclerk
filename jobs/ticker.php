<?php

/**
 * Ticker job (any exchange) - delegates out to jobs/ticker/<exchange>
 */

// get the relevant summary
$q = db()->prepare("SELECT * FROM exchanges WHERE id=? AND is_disabled=0");
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
	case "vircurex":
		require(__DIR__ . "/ticker/vircurex.php");
		break;

	case "themoneyconverter":
		require(__DIR__ . "/ticker/themoneyconverter.php");
		break;

	case "virtex":
		require(__DIR__ . "/ticker/virtex.php");
		break;

	case "cexio":
		require(__DIR__ . "/ticker/cexio.php");
		break;

	case "crypto-trade":
		require(__DIR__ . "/ticker/crypto-trade.php");
		break;

	case "cryptsy":
		require(__DIR__ . "/ticker/cryptsy.php");
		break;

	case "coins-e":
		require(__DIR__ . "/ticker/coins-e.php");
		break;

	case "justcoin":
		require(__DIR__ . "/ticker/justcoin.php");
		break;

	case "vaultofsatoshi":
		require(__DIR__ . "/ticker/vaultofsatoshi.php");
		break;

	case "kraken":
		require(__DIR__ . "/ticker/kraken.php");
		break;

	case "poloniex":
		require(__DIR__ . "/ticker/poloniex.php");
		break;

	case "itbit":
		require(__DIR__ . "/ticker/itbit.php");
		break;

	case "average":
		require(__DIR__ . "/ticker/average.php");
		break;

	default:
    // bail on any discovered exchange
    if (in_array($exchange['name'], \DiscoveredComponents\Exchanges::getKeys())) {
      crypto_log("Ignoring discovered currency " . $exchange['name']);
      break;
    }

		throw new JobException("Unknown exchange " . $exchange['name']);
		break;
}
