<?php

/**
 * An existing free user has not logged in within X days and we
 * now need to disable their account.
 */

// get the relevant user info
$user = get_user($job['arg_id']);
if (!$user) {
	throw new JobException("Cannot find user ID " . $job['arg_id']);
}

// check that they're not a premium user etc - this should never happen
if ($user['is_premium']) {
	throw new JobException("Premium user was requested to be disabled - this should not happen");
}

// update user (before sending email)
$q = db()->prepare("UPDATE users SET is_disabled=1,disabled_at=NOW() WHERE id=? LIMIT 1");
$q->execute(array($user['id']));

// construct email
if ($user['email']) {
	$disables_at = strtotime(($user['last_login'] ? $user['last_login'] : $user['created_at']) . " +" . get_site_config('user_expiry_days') . " day");
	send_user_email($user, "disable", array(
		"name" => ($user['name'] ? $user['name'] : $user['email']),
		"days" => number_format(get_site_config('user_expiry_days')),
		"disables" => iso_date($disables_at),
		"disables_text" => recent_format($disables_at, false, ""),
		"url" => absolute_url(url_for("user#user_premium")),
		"login" => absolute_url(url_for("login")),
	));
	crypto_log("Sent disabled account e-mail to " . htmlspecialchars($user['email']) . ".");

} else {
	crypto_log("User had no valid e-mail address.");
}
