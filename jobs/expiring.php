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
  // update user (before sending email)
  $q = db()->prepare("UPDATE users SET updated_at=NOW(),is_reminder_sent=1,reminder_sent=NOW() WHERE id=? LIMIT 1");
  $q->execute(array($user['id']));

  send_user_email($user, "expiring", array(
    "name" => ($user['name'] ? $user['name'] : $user['email']),
    "expires" => iso_date($user['premium_expires']),
    "expires_text" => recent_format($user['premium_expires'], false, ""),
    "prices" => get_text_premium_prices(),
    "prices_html" => get_html_premium_prices(),
    "url" => absolute_url(url_for("user#user_premium")),
  ));
  crypto_log("Sent premium expiring soon e-mail to " . htmlspecialchars($user['email']) . ".");
} else {
  crypto_log("User had no valid e-mail address.");
}
