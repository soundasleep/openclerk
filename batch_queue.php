<?php

/**
 * Batch script: look to see if we need to queue in any jobs, and then insert them in.
 */

require("inc/global.php");

if (!(isset($argv) && $argv[1] == get_site_config("automated_key")) && require_get("key") != get_site_config("automated_key"))
	throw new Exception("Invalid key");

// TODO all of these need to be duplicated for e.g. premium users

$priority = 10;	// default priority

function added_job($job) {
	echo "\n<li>Added job " . htmlspecialchars(print_r($job, true)) . "</li>";
}

// standard jobs involve an 'id' from a table and a 'user_id' from the same table
$standard_jobs = array(
	array('table' => 'addresses', 'type' => 'blockchain'),
);

foreach ($standard_jobs as $standard) {
	$q = db()->prepare("SELECT * FROM " . $standard['table'] . " WHERE last_queue < DATE_SUB(NOW(), INTERVAL 1 DAY) OR ISNULL(last_queue)");
	$q->execute();
	while ($address = $q->fetch()) {
		$job = array(
			"priority" => $priority,
			"type" => $standard['type'],
			"user_id" => $address['user_id'],
			"arg_id" => $address['id'],
		);

		// make sure the new job doesn't already exist
		$q2 = db()->prepare("SELECT * FROM jobs WHERE job_type=:type AND arg_id=:arg_id LIMIT 1");
		$q2->execute(array('type' => $job['type'], 'arg_id' => $job['arg_id']));
		if (!$q2->fetch()) {
			$q2 = db()->prepare("INSERT INTO jobs SET priority=:priority, job_type=:type, user_id=:user_id, arg_id=:arg_id");
			$q2->execute($job);
			$job['id'] = db()->lastInsertId();
			added_job($job);
		}

		// update the address
		$q2 = db()->prepare("UPDATE addresses SET last_queue=NOW() WHERE id=?");
		$q2->execute(array($address['id']));
	}
}

echo "\n<li>Complete.";
