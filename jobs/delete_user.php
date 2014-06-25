<?php

/**
 * Delete a users account (issue #241)
 */

crypto_log("Deleting user " . $job['user_id']);
$user = get_user($job['user_id']);

if (!$user) {
	throw new JobException("Could not find any user " . $job['user_id']);
}

require(__DIR__ . "/../inc/delete_user.php");
delete_user($user['id']);

crypto_log("Complete.");
