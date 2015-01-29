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
