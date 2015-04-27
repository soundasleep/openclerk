<?php

use \Openclerk\I18n;
use \Users\User;

if (!defined('NO_SESSION')) {
  // do not start a session if we've already started one
  if (!session_id()) {
    session_start();
  }

  /**
   * Track user referer for new users at signup. This persists across requests.
   */
  if (!isset($_SESSION['referer']) && isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
    $_SESSION['referer'] = $_SERVER['HTTP_REFERER'];
  }
}

header('X-Frame-Options: SAMEORIGIN');    // prevent clickhacking

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
  $q = db()->prepare("SELECT user_properties.*, users.email FROM user_properties
    JOIN users ON user_properties.id=users.id
    WHERE users.id=?");
  $q->execute(array($id));
  return $q->fetch();
}

$global_user_logged_in = false;
/**
 * Is the current user a logged in user?
 * Once called, cached across the length of the script.
 *
 * @return true if the current request is also a user, false if not. always returns false if NO_SESSION is defined
 */
function user_logged_in() {
  if (defined('NO_SESSION')) {
    // a sessionless request can never be logged in
    return false;
  }

  $user = User::getInstance(db());
  return !!$user;
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
    $q = db()->prepare("UPDATE user_properties SET is_disabled=0,logins_after_disabled=logins_after_disabled+1 WHERE id=?");
    $q->execute(array($user['id']));
  }

  // keep track of users that logged in after receiving a warning
  if ($user['is_disable_warned']) {
    $q = db()->prepare("UPDATE user_properties SET is_disable_warned=0,logins_after_disable_warned=logins_after_disable_warned+1 WHERE id=?");
    $q->execute(array($user['id']));
  }

  // update locale
  if ($user['locale']) {
    I18n::setLocale($user['locale']);
  }

  // update login time
  $query = db()->prepare("UPDATE user_properties SET last_login=NOW(),is_disabled=0 WHERE id=?");
  $query->execute(array($user["id"]));

  // if we don't have an IP set, update it now
  if (!$user["user_ip"]) {
    $q = db()->prepare("UPDATE user_properties SET user_ip=? WHERE id=?");
    $q->execute(array(user_ip(), $user['id']));
  }

}

/**
 * Complete login, setting login keys to session or cookies as necessary.
 */
function complete_login($user, $autologin) {
  global $messages;

  $user = User::getInstance(db());
  $user->persist(db());

  handle_post_login();
}

function did_autologin() {
  $user = User::getInstance(db());
  if ($user) {
    return $user->isAutoLoggedIn();
  }
}

/**
 * Get the current user ID. If nobody is logged in, redirect to the login page.
 */
function user_id() {
  require_login();
  $user = User::getInstance(db());
  return $user->getId();
}

function require_login() {
  if (defined('NO_SESSION')) {
    throw new Exception("Cannot force login for a sessionless connection");
  }
  if (!user_logged_in()) {
    // only supports GET relogins; TODO support POST relogins
    // TODO only allow destinations that are local (to prevent XSS)
    if (isset($_SERVER['REQUEST_URI'])) {
      redirect(url_for('login', array('destination' => $_SERVER['REQUEST_URI'])));
    } else {
      redirect(url_for('login'));
    }
  }
}

/**
 * Log out the current user.
 * Also disables autologin for this session.
 */
function user_logout() {
  User::logout(db());
}

$global_is_admin = null;
/**
 * Is the current user an administrator?
 * Once called, may cached across the length of the script.
 *
 * @return true if admin, false if not. always returns false if NO_SESSION is defined
 */
function is_admin() {
  if (defined('NO_SESSION')) {
    // a sessionless request can never be admin
    return false;
  }

  if (!user_logged_in()) {
    return false;
  }

  $q = db()->prepare("SELECT * FROM user_properties WHERE id=?");
  $q->execute(array(user_id()));
  $user = $q->fetch();
  return $user['is_admin'];
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
