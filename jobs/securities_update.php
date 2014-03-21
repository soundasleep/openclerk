<?php

/**
 * Securites update job (any exchange) - delegates out to jobs/securities_update/<type>
 * Also processes the unpaid balances for eligius mining pool.
 */

// get the relevant summary
$q = db()->prepare("SELECT * FROM securities_update WHERE id=?");
$q->execute(array($job['arg_id']));
$exchange = $q->fetch();
if (!$exchange) {
	throw new JobException("Cannot find an securities update " . $job['arg_id']);
}

// what kind of exchange is it?
// each exchange will insert in many different currency pairs, depending on how many
// currencies are supported
switch ($exchange['exchange']) {
	case "btct":
		require(__DIR__ . "/securities_update/btct.php");
		break;

	case "havelock":
		require(__DIR__ . "/securities_update/havelock.php");
		break;

	case "eligius":
		require(__DIR__ . "/securities_update/eligius.php");
		break;

	case "litecoininvest":
		require(__DIR__ . "/securities_update/litecoininvest.php");
		break;

	case "btcinve":
		require(__DIR__ . "/securities_update/btcinve.php");
		break;

	default:
		throw new JobException("Unknown securities update exchange " . $exchange['exchange']);
		break;
}
