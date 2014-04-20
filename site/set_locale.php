<?php

/**
 * Set the current session, cookie or user language.
 */

require(__DIR__ . "/../inc/global.php");

$locale = require_post("locale");
$redirect = require_post("redirect");
if (!in_array($locale, get_all_locales())) {
	throw new LocaleException("Locale '$locale' does not exist for user selection");
}

set_locale($locale);

// update cookies
setcookie('locale', $locale, time() + (60 * 60 * 24 * 365 * 10) /* 10 years in the future */);

// update users
if (user_logged_in()) {
	$user = get_user(user_id());

	$q = db()->prepare("UPDATE users SET locale=? WHERE id=?");
	$q->execute(array($locale, user_id()));
}

// go back to their previous page
redirect($redirect);
