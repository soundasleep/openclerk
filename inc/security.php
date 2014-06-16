<?php

session_start();
header('X-Frame-Options: SAMEORIGIN');		// prevent clickhacking

/**
 * Track user referer for new users at signup. This persists across requests.
 */
if (!isset($_SESSION['referer']) && isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
	$_SESSION['referer'] = $_SERVER['HTTP_REFERER'];
}

/**
 * Get the user with this particular ID.
 * Does not cache the results of this function.
 * Does not throw an exception if no user exists.
 * @see user_id()
 */
function get_user($id) {
	if (!$id) {
		throw new Exception("No ID specified.");
	}
	$query = db()->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
	$query->execute(array($id));
	return $query->fetch();
}

$global_user_logged_in = false;
function user_logged_in() {
	// cache the results of this function
	// we don't cache failed login results, in case we login later through this script
	global $global_user_logged_in;
	if ($global_user_logged_in) {
		return $global_user_logged_in;
	}

	// try autologin if we haven't logged in at all yet
	if (!isset($_SESSION["user_id"]) && !isset($_SESSION["user_key"]))
		try_autologin();

	if (!isset($_SESSION["user_id"]))
		return false;
	if (!isset($_SESSION["user_key"]))
		return false;

	$user_id = $_SESSION["user_id"];
	$user_key = $_SESSION["user_key"];

	// query the database to check that we have a valid user key
	$query = db()->prepare("SELECT * FROM valid_user_keys WHERE user_id=? AND user_key=? LIMIT 1");
	$query->execute(array($user_id, $user_key));
	if (!$query->fetch()) {
		return false;
	}

	// we're logged in successfully
	$global_user_logged_in = true;
	return true;
}

/**
 * Call this function only after we have successfully logged in.
 * Updates user status etc.
 */
function handle_post_login() {
	global $messages;
	if (!isset($messages)) {
		// we might be in auto-login, create a temporary message field anyway
		$messages = array();
	}

	$user = get_user(user_id());

	// display warning if account was disabled
	if ($user['is_disabled']) {
		$messages[] = t("Your account was disabled :ago due to inactivity; your account is now re-enabled, and account data will be updated again soon.",
			array(':ago' => recent_format($user['disabled_at'])));
		$q = db()->prepare("UPDATE users SET is_disabled=0,logins_after_disabled=logins_after_disabled+1 WHERE id=?");
		$q->execute(array($user['id']));
	}

	// keep track of users that logged in after receiving a warning
	if ($user['is_disable_warned']) {
		$q = db()->prepare("UPDATE users SET is_disable_warned=0,logins_after_disable_warned=logins_after_disable_warned+1 WHERE id=?");
		$q->execute(array($user['id']));
	}

	// update locale
	if ($user['locale']) {
		set_locale($user['locale']);
	}

	// update login time
	$query = db()->prepare("UPDATE users SET last_login=NOW(),is_disabled=0 WHERE id=?");
	$query->execute(array($user["id"]));

	// if we don't have an IP set, update it now
	if (!$user["user_ip"]) {
		$q = db()->prepare("UPDATE users SET user_ip=? WHERE id=?");
		$q->execute(array(user_ip(), $user['id']));
	}

}

// global variables for autologin
$global_did_autologin = false;
$global_tried_autologin = false;

/**
 * Try autologin. This function is only executed if we actually <em>make</em> a validation check,
 * e.g. pages or scripts that don't require login information won't need to automatically log in.
 * Autologin does not re-request the remote OpenID server.
 * @see #did_autologin()
 */
function try_autologin() {
	global $global_tried_autologin;
	if ($global_tried_autologin) return; // we only want to try autologin once: cookies are only ever available once

	$global_tried_autologin = true;

	if (isset($_COOKIE["autologin_id"]) && isset($_COOKIE["autologin_key"]) && !isset($_SESSION["autologin_disable"])) {
		// test
		$query = db()->prepare("SELECT * FROM valid_user_keys WHERE user_id=? AND user_key=? LIMIT 1");
		$query->execute(array($_COOKIE["autologin_id"], $_COOKIE["autologin_key"]));
		if (!$query->fetch()) {
			return false;
		}

		// get user
		// try OpenID user first
		$query = db()->prepare("SELECT * FROM users WHERE id=?");
		$query->execute(array($_COOKIE["autologin_id"]));
		if (!($user = $query->fetch())) {
			// no valid user in the database
			return false;
		}

		// apply session data
		$_SESSION["user_id"] = $_COOKIE["autologin_id"];
		$_SESSION["user_key"] = $_COOKIE["autologin_key"]; // uses the same login key
		$_SESSION["user_name"] = $user["name"];

		// display warning if account was disabled
		if ($user['is_disabled']) {
			global $global_temporary_messages;
			if (!is_array($global_temporary_messages)) {
				$global_temporary_messages = array();
			}
			$global_temporary_messages[] = t("Your account was disabled :ago due to inactivity; your account is now re-enabled, and account data will be updated again soon.",
				array(':ago' => recent_format($user['disabled_at'])));
		}

		// handle post-login
		handle_post_login();

		global $global_did_autologin;
		$global_did_autologin = true;
	}

}

/**
 * Complete login, setting login keys to session or cookies as necessary.
 */
function complete_login($user, $autologin) {
	// immediately mark user as logged in
	global $global_user_logged_in;
	$global_user_logged_in = true;

	// delete old web keys
	$query = db()->prepare("DELETE FROM valid_user_keys WHERE user_id=? AND DATEDIFF(NOW(), created_at) > ?");
	$query->execute(array($user["id"], get_site_config("autologin_expire_days") /* maximum length of autologin key or web key */ ));

	// create new login key
	$user_key = sprintf("%04x%04x%04x%04x", rand(0,0xffff), rand(0,0xffff), rand(0,0xffff), rand(0,0xffff));
	$query = db()->prepare("INSERT INTO valid_user_keys SET user_id=?, user_key=?, created_at=NOW()");
	$query->execute(array($user["id"], $user_key));

	// update session data
	$_SESSION["user_id"] = $user["id"];
	$_SESSION["user_key"] = $user_key;
	$_SESSION["user_name"] = $user["name"];
	$_SESSION["autologin_disable"] = 0;
	unset($_SESSION["autologin_disable"]);

	// update autologin
	if ($autologin) {
		setcookie('autologin_id', $user["id"], time() + get_site_config("autologin_cookie_seconds"));
		setcookie('autologin_key', $user_key, time() + get_site_config("autologin_cookie_seconds"));
	} else {
		// remove any autologin
		setcookie('autologin_id', "", time() - 3600);
		setcookie('autologin_key', "", time() - 3600);
	}

	// handle post-login
	handle_post_login();

}

function did_autologin() {
	global $global_did_autologin;
	return $global_did_autologin;
}

/**
 * Get the current user ID. If nobody is logged in, redirect to the login page.
 */
function user_id() {
	require_login();
	return require_session("user_id");
}

function user_ip() {
	return $_SERVER['REMOTE_ADDR'];
	// also see $_SERVER['HTTP_X_FORWARDED_FOR'] for (possibly spoofed) proxy address
}

function require_login() {
	if (!user_logged_in()) {
		// only supports GET relogins; TODO support POST relogins
		// TODO only allow destinations that are local (to prevent XSS)
		redirect(url_for('login', array('destination' => $_SERVER['REQUEST_URI'])));
	}
}

$global_is_admin = null;
/**
 * Is the current user an administrator?
 * Once called, persists across the length of the script.
 *
 * @return true if admin, false if not
 */
function is_admin() {
	global $global_is_admin;
	if ($global_is_admin === null) {
		if (!user_logged_in()) {
			$global_is_admin = false;
		} else {
			$user = get_user(user_id());
			$global_is_admin = $user["is_admin"];
		}
	}
	return $global_is_admin;
}

// so we don't have to have 'is_admin' flag in templates (which doesn't guarantee we've checked)
$has_required_admin = false;

function require_admin() {
	global $has_required_admin;
	$has_required_admin = true;
	require_login();
	if (!is_admin()) {
		// only supports GET relogins; TODO support POST relogins
		redirect(url_for('login', array('need_admin' => 1, 'destination' => $_SERVER['REQUEST_URI'])));
	}
}

function has_required_admin() {
	global $has_required_admin;
	return $has_required_admin;
}

class SecurityException extends Exception { }

function require_user($user) {
	global $errors;
	if (!$user) {
		if (!$errors) {
			$errors = array();
		}
		$errors[] = t("Could not find your profile on the system. You will need to login or signup again.");
		set_temporary_errors($errors);
		redirect(url_for('login'));
	}
}
