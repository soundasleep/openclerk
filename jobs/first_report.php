<?php

/**
 * Send an e-mail to new users once their first non-zero summary reports have been compiled.
 */

// get the relevant user info
$user = get_user($job['arg_id']);
if (!$user) {
	throw new JobException("Cannot find user ID " . $job['arg_id']);
}

if ($user['is_first_report_sent']) {
	throw new JobException("User has already had a first report sent: " . $user['id']);
}

// is there a non-zero summary instance?
$q = db()->prepare("SELECT * FROM summary_instances WHERE user_id=? AND is_recent=1 AND balance > 0 LIMIT 1");
$q->execute(array($user['id']));
if ($instance = $q->fetch()) {
	crypto_log("User has a non-zero summary instance.");

	// update that we've reported now
	$q = db()->prepare("UPDATE users SET is_first_report_sent=1 WHERE id=?");
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
