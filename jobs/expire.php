<?php

/**
 * An existing premium user's account needs to expire.
 * May send out an e-mail.
 */

// get the relevant user info
$user = get_user($job['arg_id']);
if (!$user) {
	throw new JobException("Cannot find user ID " . $job['arg_id']);
}

$q = db()->prepare("UPDATE users SET updated_at=NOW(),is_premium=0 WHERE id=? LIMIT 1");
$q->execute(array($user['id']));
crypto_log("Disabled premium status on user " . $user['id'] . ".");

// construct email
if ($user['email']) {
	send_email($user['email'], ($user['name'] ? $user['name'] : $user['email']), "expire", array(
		"name" => ($user['name'] ? $user['name'] : $user['email']),
		"expires" => iso_date($user['premium_expires']),
		"expires_text" => recent_format($user['premium_expires'], false, ""),
		"prices" => get_text_premium_prices(),
		"url" => absolute_url(url_for("user")),
	));
	crypto_log("Sent premium expired soon e-mail to " . htmlspecialchars($user['email']) . ".");
} else {
	crypto_log("User had no valid e-mail address.");
}
