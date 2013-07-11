<?php

/**
 * An existing free user has not logged in within X days and we
 * want to warn them that they need to login before their user account
 * is disabled.
 */

// get the relevant user info
$user = get_user($job['arg_id']);
if (!$user) {
	throw new JobException("Cannot find user ID " . $job['arg_id']);
}

// check that they're not a premium user etc - this should never happen
if ($user['is_premium']) {
	throw new JobException("Premium user was requested to be warned of disabled - this should not happen");
} else if ($user['is_disabled']) {
	throw new JobException("Disabled user was requested to be warned of disabled - this should not happen");
}

$disables_at = strtotime(($user['last_login'] ? $user['last_login'] : $user['created_at']) . " +" . get_site_config('user_expiry_days') . " day");

if ($disables_at > time()) {
	// there's no point in sending an email if it's going to be disabled in the past; it will be disabled on our very next run anyway

	// construct email
	if ($user['email']) {
		send_email($user['email'], ($user['name'] ? $user['name'] : $user['email']), "disable_warning", array(
			"name" => ($user['name'] ? $user['name'] : $user['email']),
			"days" => number_format(get_site_config('user_expiry_days')),
			"disables" => iso_date($disables_at),
			"disables_text" => recent_format($disables_at, false, ""),
			"url" => absolute_url(url_for("user")),
			"login" => absolute_url(url_for("login")),
		));
		crypto_log("Sent disable warning soon e-mail to " . htmlspecialchars($user['email']) . ".");

	} else {
		crypto_log("User had no valid e-mail address.");
	}
} else {
	crypto_log("Did not send any disable warning: disable time is set into the past (" . iso_date($disables_at) . ")");
}

// update user
$q = db()->prepare("UPDATE users SET is_disable_warned=1,disable_warned_at=NOW() WHERE id=? LIMIT 1");
$q->execute(array($user['id']));
