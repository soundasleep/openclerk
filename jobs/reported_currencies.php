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

	default:
		throw new JobException("Unknown exchange " . $exchange['name']);
		break;
}
