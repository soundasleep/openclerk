<?php

/**
 * An existing premium user's account is about to expire.
 * Only valid if the user has an e-mail address.
 */

// get the relevant user info
$user = get_user($job['arg_id']);
if (!$user) {
	throw new JobException("Cannot find user ID " . $job['arg_id']);
}

// construct email
if ($user['email']) {
	send_email($user['email'], ($user['name'] ? $user['name'] : $user['email']), "expiring", array(
		"name" => ($user['name'] ? $user['name'] : $user['email']),
		"expires" => iso_date($user['premium_expires']),
		"expires_text" => recent_format($user['premium_expires'], false, ""),
		"url" => absolute_url(url_for("user")),
	));
	crypto_log("Sent premium expiring soon e-mail to " . htmlspecialchars($user['email']) . ".");

	// update user
	$q = db()->prepare("UPDATE users SET updated_at=NOW(),is_reminder_sent=1 WHERE id=? LIMIT 1");
	$q->execute(array($user['id']));
} else {
	crypto_log("User had no valid e-mail address.");
}
