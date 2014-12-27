<?php

require_login();

$user = get_user(user_id());

$messages = array();
$errors = array();

$confirm = require_post("confirm_text");
$reason = require_post("reason", "");

if ($confirm != "confirm") {
  $errors[] = t("Please type in ':text'.", array(':text' => "confirm"));
}

if (!$errors) {
  // mark the account as due to be deleted
  $q = db()->prepare("UPDATE users SET is_deleted=1, requested_delete_at=NOW() WHERE id=?");
  $q->execute(array(user_id()));

  // remove any OpenID connections for this user account, so that users can sign up
  // again immediately with the same OpenID details
  $q = db()->prepare("DELETE FROM openid_identities WHERE user_id=?");
  $q->execute(array(user_id()));

  // send email to user
  $name = $user['name'] ? $user['name'] : $user['email'];
  $email = $user['email'];
  if ($email) {
    send_user_email($user, "deleted", array(
      "email" => $email,
      "name" => $name,
      "url" => absolute_url(url_for("signup")),
    ));
  }

  // send email to admin with reasons
  $email = get_site_config('site_email');
  send_email($email, "deleted_reason", array(
    "email" => $email,
    "reason" => $reason,
    "user" => print_r($user, true),
  ));

  // redirect back to signup page with information
  $messages[] = t("Your user account will shortly be deleted. You may sign up again here.");

  // now logout the user before doing anything else!
  user_logout();

  set_temporary_messages($messages);
  set_temporary_errors($errors);
  redirect(url_for('signup'));

}

// otherwise, go back to user page with errors
set_temporary_messages($messages);
set_temporary_errors($errors);

redirect(url_for('user#user_delete'));
