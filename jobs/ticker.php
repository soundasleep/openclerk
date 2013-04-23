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
		require("jobs/ticker/btce.php");
		break;

	case "bitnz_disabled":
		require("jobs/ticker/bitnz.php");
		break;

	default:
		throw new JobException("Unknown exchange " . $exchange['name']);
		break;
}
