<?php

/**
 * Summary job (any currency) - delegates out to jobs/summary/<summary-type>
 */

// get the relevant user info
$user = get_user($job['user_id']);
if (!$user) {
	throw new JobException("Cannot find user ID " . $job['user_id']);
}

// get the relevant summary
$q = db()->prepare("SELECT * FROM summaries WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$summary = $q->fetch();
if (!$summary) {
	throw new JobException("Cannot find a summary " . $job['arg_id'] . " for user " . $job['user_id']);
}

// if a 'sum' job is currently running for this user, wait until it is finished
// this should help situations where summary jobs calculate incorrect values (due to race conditions) -
// e.g. set all totalbtc to is_recent=0 and this job executes before a new totalbtc is_recent=1 is inserted
$q = db()->prepare("SELECT id FROM jobs WHERE user_id=? AND job_type=? AND is_executing=1 LIMIT 1");
$q->execute(array($job['user_id'], 'sum'));
if ($sum = $q->fetch()) {
	throw new JobException("Cannot execute summary job yet; waiting for sum job " . $sum['id'] . " to finish first.");
}

// what kind of summary is it?
// each job include will set a runtime value $total.
// this will set a runtime value $total.
switch ($summary['summary_type']) {
	case "summary_btc":
		$total = 0;
		require(__DIR__ . "/summary/crypto2btc.php");
		add_summary_instance($job, 'crypto2btc', $total);

		// TODO all2btc
		break;

	case "summary_ltc":
		$total = 0;
		require(__DIR__ . "/summary/crypto2ltc.php");
		add_summary_instance($job, 'crypto2ltc', $total);

		// TODO all2ltc
		break;

	case "summary_nmc":
		$total = 0;
		require(__DIR__ . "/summary/crypto2nmc.php");
		add_summary_instance($job, 'crypto2nmc', $total);

		// TODO all2nmc
		break;

	case "summary_ftc":
		$total = 0;
		require(__DIR__ . "/summary/crypto2ftc.php");
		add_summary_instance($job, 'crypto2ftc', $total);

		// TODO all2ftc
		break;

	case "summary_ppc":
		$total = 0;
		require(__DIR__ . "/summary/crypto2ppc.php");
		add_summary_instance($job, 'crypto2ppc', $total);

		// TODO all2ppc
		break;

	case "summary_nvc":
		$total = 0;
		require(__DIR__ . "/summary/crypto2nvc.php");
		add_summary_instance($job, 'crypto2nvc', $total);

		// TODO all2ppc
		break;

	case "summary_usd_btce":
		// TODO fiat2usd

		$total = 0;
		require(__DIR__ . "/summary/all2usd_btce.php");
		add_summary_instance($job, 'all2usd_btce', $total);
		break;

	case "summary_usd_mtgox":
		// TODO fiat2usd

		$total = 0;
		require(__DIR__ . "/summary/all2usd_mtgox.php");
		add_summary_instance($job, 'all2usd_mtgox', $total);
		break;

	case "summary_usd_vircurex":
		// TODO fiat2usd

		$total = 0;
		require(__DIR__ . "/summary/all2usd_vircurex.php");
		add_summary_instance($job, 'all2usd_vircurex', $total);
		break;

	case "summary_usd_bitstamp":
		// TODO fiat2usd

		$total = 0;
		require(__DIR__ . "/summary/all2usd_bitstamp.php");
		add_summary_instance($job, 'all2usd_bitstamp', $total);
		break;

	case "summary_eur_btce":
		// TODO fiat2usd

		$total = 0;
		require(__DIR__ . "/summary/all2eur_btce.php");
		add_summary_instance($job, 'all2eur_btce', $total);
		break;

	case "summary_eur_mtgox":
		// TODO fiat2eur

		$total = 0;
		require(__DIR__ . "/summary/all2eur_mtgox.php");
		add_summary_instance($job, 'all2eur_mtgox', $total);
		break;

	case "summary_eur_vircurex":
		// TODO fiat2eur

		$total = 0;
		require(__DIR__ . "/summary/all2eur_vircurex.php");
		add_summary_instance($job, 'all2eur_vircurex', $total);
		break;

	case "summary_aud_mtgox":
		// TODO fiat2aud

		$total = 0;
		require(__DIR__ . "/summary/all2aud_mtgox.php");
		add_summary_instance($job, 'all2aud_mtgox', $total);
		break;

	case "summary_cad_mtgox":
		// TODO fiat2cad

		$total = 0;
		require(__DIR__ . "/summary/all2cad_mtgox.php");
		add_summary_instance($job, 'all2cad_mtgox', $total);
		break;

	case "summary_cad_virtex":
		// TODO fiat2cad

		$total = 0;
		require(__DIR__ . "/summary/all2cad_virtex.php");
		add_summary_instance($job, 'all2cad_virtex', $total);
		break;

	case "summary_nzd_bitnz":
		// TODO fiat2nzd

		$total = 0;
		require(__DIR__ . "/summary/all2nzd_bitnz.php");
		add_summary_instance($job, 'all2nzd_bitnz', $total);
		break;

	case "summary_ghs":
		$total = 0;
		require(__DIR__ . "/summary/crypto2ghs.php");
		add_summary_instance($job, 'crypto2ghs', $total);

		// TODO all2ghs
		break;

	default:
		throw new JobException("Unknown summary type " . $summary['summary_type']);
		break;
}

// and now that we have added summary instances, check for first_report
// (this is so that first_report jobs don't block up the job queue)

/**
 * Send an e-mail to new users once their first non-zero summary reports have been compiled.
 */

if (!$user['is_first_report_sent']) {
	// is there a non-zero summary instance?
	$q = db()->prepare("SELECT * FROM summary_instances WHERE user_id=? AND is_recent=1 AND balance > 0 LIMIT 1");
	$q->execute(array($user['id']));
	if ($instance = $q->fetch()) {
		crypto_log("User has a non-zero summary instance.");

		// update that we've reported now
		$q = db()->prepare("UPDATE users SET is_first_report_sent=1,first_report_sent=NOW() WHERE id=?");
		$q->execute(array($user['id']));

		// send email
		if ($user['email']) {
			send_email($user['email'], ($user['name'] ? $user['name'] : $user['email']), "first_report", array(
				"name" => ($user['name'] ? $user['name'] : $user['email']),
				"url" => absolute_url(url_for("profile")),
				"login" => absolute_url(url_for("login")),
				// TODO in the future this will have reporting values (when automatic reports are implemented)
			));
			crypto_log("Sent first report e-mail to " . htmlspecialchars($user['email']) . ".");
		}

	}
}
