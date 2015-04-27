<?php

/**
 * This admin-only scripts allows us to "login" as another user for debugging
 * purposes. First the user must be logged in as an administrator, and the
 * 'allow_fake_login' config parameter set to true.
 * The target user also must not be an administrator.
 */

require_admin();

// TODO need to migrate to new openclerk/users framework
throw new Exception("Not implemented (#266).");

if (!get_site_config('allow_fake_login')) {
  throw new Exception("Fake login must be enabled through 'allow_fake_login' first.");
}

// login as a new user
$query = db()->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
$query->execute(array(require_get("id")));
if (!($user = $query->fetch())) {
  throw new Exception("No user account found: " . require_get("id"));
}

if ($user['is_admin']) {
  throw new Exception("Cannot login as an administrator");
}

// create a log message
class FakeLogin extends Exception { }
log_uncaught_exception(new FakeLogin("Login emulated for user " . $user['id']));

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

// remove any autologin
setcookie('autologin_id', "", time() - 3600);
setcookie('autologin_key', "", time() - 3600);

// redirect to graphs page
redirect(url_for('profile'));
