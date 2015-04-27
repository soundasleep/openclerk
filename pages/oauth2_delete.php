<?php

/**
 * Allows users to delete OAuth2 locations from their account.
 */

require_login();

$messages = array();
$errors = array();

$uid = require_post("uid");
$provider = require_post("provider");

// make sure we aren't deleting our last identity
$q = db()->prepare("SELECT COUNT(*) AS c FROM user_oauth2_identities WHERE user_id=?");
$q->execute(array(user_id()));
$count = $q->fetch();

// or we have an OpenID identity
$q = db()->prepare("SELECT * FROM user_openid_identities WHERE user_id=? LIMIT 1");
$q->execute(array(user_id()));
$openid = $q->fetch();

// or we have a password hash
$q = db()->prepare("SELECT * FROM user_passwords WHERE user_id=?");
$q->execute(array(user_id()));
$password_hash = $q->fetch();

if ($count['c'] <= 1 && !$password_hash && !$openid) {
  $errors[] = t("Cannot remove that OAuth2 identity; at least one identity must be defined.");
  set_temporary_messages($messages);
  set_temporary_errors($errors);
  redirect(url_for('user#user_openid'));
}

$user = \Users\User::getInstance(db());
\Users\UserOAuth2::removeIdentity(db(), $user, $provider, $uid);
$messages[] = t("Removed OAuth2 identity ':identity'.", array(':identity' => $provider));

set_temporary_messages($messages);
set_temporary_errors($errors);
redirect(url_for('user#user_openid'));
