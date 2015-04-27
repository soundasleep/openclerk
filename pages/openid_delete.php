<?php

/**
 * Allows users to delete OpenID locations from their account.
 */

require_login();

$messages = array();
$errors = array();

$identity = require_post("identity");

// make sure we aren't deleting our last identity
$q = db()->prepare("SELECT COUNT(*) AS c FROM user_openid_identities WHERE user_id=?");
$q->execute(array(user_id()));
$count = $q->fetch();

// or we have a password hash
$q = db()->prepare("SELECT * FROM user_passwords WHERE user_id=?");
$q->execute(array(user_id()));
$password_hash = $q->fetch();

if ($count['c'] <= 1 && !$password_hash) {
  $errors[] = t("Cannot remove that OpenID identity; at least one identity must be defined.");
  set_temporary_messages($messages);
  set_temporary_errors($errors);
  redirect(url_for('user#user_openid'));
}

$user = \Users\User::getInstance(db());
\Users\UserOpenID::removeIdentity(db(), $user, $identity);
$messages[] = t("Removed OpenID identity ':identity'.", array(':identity' => $identity));

set_temporary_messages($messages);
set_temporary_errors($errors);
redirect(url_for('user#user_openid'));
