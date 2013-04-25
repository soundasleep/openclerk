<?php

/**
 * Summary job (any currency) - delegates out to jobs/summary/<summary-type>
 */

// get the relevant summary
$q = db()->prepare("SELECT * FROM summaries WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$summary = $q->fetch();
if (!$summary) {
	throw new JobException("Cannot find an summary " . $job['arg_id'] . " for user " . $job['user_id']);
}

// what kind of summary is it?
// this will set a runtime value $total.
$total = 0;
switch ($summary['summary_type']) {
	case "totalbtc":
		require("jobs/summary/totalbtc.php");
		break;

	case "totalltc":
		require("jobs/summary/totalltc.php");
		break;

	case "totalnmc":
		require("jobs/summary/totalnmc.php");
		break;

	case "totalusd":
		require("jobs/summary/totalusd.php");
		break;

	case "totalnzd":
		require("jobs/summary/totalnzd.php");
		break;

	case "all2btc":
		require("jobs/summary/all2btc.php");
		break;

	case "all2nzd":
		require("jobs/summary/all2nzd.php");
		break;

	case "all2usd_btce":
		require("jobs/summary/all2usd_btce.php");
		break;

	case "all2usd_mtgox":
		require("jobs/summary/all2usd_mtgox.php");
		break;

	default:
		throw new JobException("Unknown summary type " . $summary['summary_type']);
		break;
}

// update old summaries
$q = db()->prepare("UPDATE summary_instances SET is_recent=0 WHERE is_recent=1 AND user_id=? AND summary_type=?");
$q->execute(array($job['user_id'], $summary['summary_type']));

// insert new summary
$q = db()->prepare("INSERT INTO summary_instances SET is_recent=1, user_id=:user_id, summary_type=:summary_type, balance=:balance");
$q->execute(array(
	"user_id" => $job['user_id'],
	"summary_type" => $summary['summary_type'],
	"balance" => $total,
));
crypto_log("Inserted new summary_instances id=" . db()->lastInsertId());
