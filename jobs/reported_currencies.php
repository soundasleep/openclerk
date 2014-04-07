<?php

/**
 * Reported currencies job (any exchange) - delegates out to jobs/reported_currencies/<exchange>
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
	case "vaultofsatoshi":
		require(__DIR__ . "/reported_currencies/vaultofsatoshi.php");
		break;

	case "btce":
		require(__DIR__ . "/reported_currencies/btce.php");
		break;

	case "cexio":
		require(__DIR__ . "/reported_currencies/cexio.php");
		break;

	case "coinbase":
		require(__DIR__ . "/reported_currencies/coinbase.php");
		break;

	case "coins-e":
		require(__DIR__ . "/reported_currencies/coins-e.php");
		break;

	case "crypto-trade":
		require(__DIR__ . "/reported_currencies/crypto-trade.php");
		break;

	default:
		throw new JobException("Unknown exchange to report currencies " . $exchange['name']);
		break;
}
