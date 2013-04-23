<?php

session_start();

/**
 * Get the user with this particular ID.
 * Does not cache the results of this function.
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

// global variables for autologin
$global_did_autologin = false;
$global_tried_autologin = false;

/**
 * Try autologin. This function is only executed if we actually <em>make</em> a validation check,
 * e.g. pages or scripts that don't require login information won't need to automatically log in.
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
		} else {
			// apply session data
			$_SESSION["user_id"] = $_COOKIE["autologin_id"];
			$_SESSION["user_key"] = $_COOKIE["autologin_key"]; // uses the same login key
			$_SESSION["user_name"] = $user["name"];
		}

		global $global_did_autologin;
		$global_did_autologin = true;
	}

}

function did_autologin() {
	global $global_did_autologin;
	return $global_did_autologin;
}

function user_id() {
    return require_session("user_id");
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

function require_admin() {
	require_login();
	if (!is_admin()) {
		// only supports GET relogins; TODO support POST relogins
		redirect(url_for('login', array('need_admin' => 1, 'destination' => $_SERVER['REQUEST_URI'])));
	}
}

/**
 * Try a conventional user login (email, password). Does not modify the database or session in any way.
 * Also does not check to see whether the user is verified or not.
 *
 * @return true if login was successful, false otherwise.
 */
function try_login($email, $password) {
	throw new Exception("Login is not supported yet");
	$query = db()->prepare("SELECT * FROM users_conventional WHERE email=? AND password_hash=? LIMIT 1");
	$query->execute(array($email, hash_password($password)));
	if (!($result = $query->fetch())) {
		return false;
	}

	return $result;
}

/**
 * Update the password of the given user. Also resets all existing login keys, both web and client.
 */
function set_password($email, $password) {
	throw new Exception("Set password is not supported yet");
	// get the user
	$query = db()->prepare("SELECT * FROM users_conventional WHERE email=? LIMIT 1");
	$query->execute(array($email));
	if (!($user = $query->fetch())) {
		throw new Exception("No such user found for e-mail address '$email'");
	}

	// update hash
	$query = db()->prepare("UPDATE users_conventional SET password_hash=? WHERE email=? LIMIT 1");
	$query->execute(array(hash_password($password), $email));

	// remove all valid keys
	$query = db()->prepare("DELETE FROM valid_user_keys WHERE user_id=?");
	$query->execute(array($user["id"]));

}

function hash_password($password) {
	return md5(get_site_config('password_salt') . $password);
}

function is_valid_password($p) {
	return strlen($p) >= 6;
}
function valid_password_reason() {
	return "is at least six characters long";
}

class SecurityException extends Exception { }