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
		require("jobs/summary/crypto2btc.php");
		add_summary_instance($job, 'crypto2btc', $total);

		// TODO all2btc
		break;

	case "summary_ltc":
		$total = 0;
		require("jobs/summary/crypto2ltc.php");
		add_summary_instance($job, 'crypto2ltc', $total);

		// TODO all2ltc
		break;

	case "summary_nmc":
		$total = 0;
		require("jobs/summary/crypto2nmc.php");
		add_summary_instance($job, 'crypto2nmc', $total);

		// TODO all2nmc
		break;

	case "summary_ftc":
		$total = 0;
		require("jobs/summary/crypto2ftc.php");
		add_summary_instance($job, 'crypto2ftc', $total);

		// TODO all2ftc
		break;

	case "summary_usd_btce":
		// TODO fiat2usd

		$total = 0;
		require("jobs/summary/all2usd_btce.php");
		add_summary_instance($job, 'all2usd_btce', $total);
		break;

	case "summary_usd_mtgox":
		// TODO fiat2usd

		$total = 0;
		require("jobs/summary/all2usd_mtgox.php");
		add_summary_instance($job, 'all2usd_mtgox', $total);
		break;

	case "summary_usd_vircurex":
		// TODO fiat2usd

		$total = 0;
		require("jobs/summary/all2usd_vircurex.php");
		add_summary_instance($job, 'all2usd_vircurex', $total);
		break;

	case "summary_eur_btce":
		// TODO fiat2usd

		$total = 0;
		require("jobs/summary/all2eur_btce.php");
		add_summary_instance($job, 'all2eur_btce', $total);
		break;

	case "summary_eur_mtgox":
		// TODO fiat2eur

		$total = 0;
		require("jobs/summary/all2eur_mtgox.php");
		add_summary_instance($job, 'all2eur_mtgox', $total);
		break;

	case "summary_eur_vircurex":
		// TODO fiat2eur

		$total = 0;
		require("jobs/summary/all2eur_vircurex.php");
		add_summary_instance($job, 'all2eur_vircurex', $total);
		break;

	case "summary_aud_mtgox":
		// TODO fiat2aud

		$total = 0;
		require("jobs/summary/all2aud_mtgox.php");
		add_summary_instance($job, 'all2aud_mtgox', $total);
		break;

	case "summary_nzd":
		// TODO fiat2nzd

		$total = 0;
		require("jobs/summary/all2nzd.php");
		add_summary_instance($job, 'all2nzd', $total);
		break;

	default:
		throw new JobException("Unknown summary type " . $summary['summary_type']);
		break;
}
