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

	default:
		throw new JobException("Unknown exchange " . $exchange['name']);
		break;
}
