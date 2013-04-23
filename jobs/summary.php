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

	default:
		throw new JobException("Unknown summary type " . $summary['summary_type']);
		break;
}
