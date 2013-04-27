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
// each job include will set a runtime value $total.
// this will set a runtime value $total.
switch ($summary['summary_type']) {
	case "summary_btc":
		$total = 0;
		require("jobs/summary/totalbtc.php");
		add_summary_instance($job, 'totalbtc', $total);
		$total = 0;
		require("jobs/summary/all2btc.php");
		add_summary_instance($job, 'all2btc', $total);
		break;

	case "summary_ltc":
		$total = 0;
		require("jobs/summary/totalltc.php");
		add_summary_instance($job, 'totalltc', $total);
		// TODO all2ltc
		break;

	case "summary_nmc":
		$total = 0;
		require("jobs/summary/totalnmc.php");
		add_summary_instance($job, 'totalnmc', $total);
		// TODO all2nmc
		break;

	case "summary_usd_btce":
		$total = 0;
		require("jobs/summary/totalusd.php");
		add_summary_instance($job, 'totalusd', $total);
		$total = 0;
		require("jobs/summary/all2usd_btce.php");
		add_summary_instance($job, 'all2usd_btce', $total);
		break;

	case "summary_usd_mtgox":
		$total = 0;
		require("jobs/summary/totalusd.php");
		add_summary_instance($job, 'totalusd', $total);
		$total = 0;
		require("jobs/summary/all2usd_mtgox.php");
		add_summary_instance($job, 'all2usd_mtgox', $total);
		break;

	case "summary_nzd":
		$total = 0;
		require("jobs/summary/totalnzd.php");
		add_summary_instance($job, 'totalnzd', $total);
		$total = 0;
		require("jobs/summary/all2nzd.php");
		add_summary_instance($job, 'all2nzd', $total);
		break;

	default:
		throw new JobException("Unknown summary type " . $summary['summary_type']);
		break;
}

function add_summary_instance($job, $summary_type, $total) {

	// update old summaries
	$q = db()->prepare("UPDATE summary_instances SET is_recent=0 WHERE is_recent=1 AND user_id=? AND summary_type=?");
	$q->execute(array($job['user_id'], $summary_type));

	// insert new summary
	$q = db()->prepare("INSERT INTO summary_instances SET is_recent=1, user_id=:user_id, summary_type=:summary_type, balance=:balance");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"summary_type" => $summary_type,
		"balance" => $total,
	));
	crypto_log("Inserted new summary_instances id=" . db()->lastInsertId());

}
