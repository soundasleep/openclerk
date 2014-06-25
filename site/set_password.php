<?php

require(__DIR__ . "/../inc/global.php");
require_login();

$user = get_user(user_id());

$messages = array();
$errors = array();

$password = require_post("password", false);
$password2 = require_post("password2", false);

if ($password && (strlen($password) < 6 || strlen($password) > 255)) {
	$errors[] = t("Please select a password between :min-:max characters long.", array(':min' => 6, ':max' => 255));
}
if ($password && $password != $password2) {
	$errors[] = t("Those passwords do not match.");
}
if (!$user['email']) {
	$errors[] = t("You need to have added an e-mail address to your account before you can enable password login.");
}

// check there are no other accounts using a password hash on this e-mail address
$q = db()->prepare("SELECT * FROM users WHERE email=? AND ISNULL(password_hash) = 0 AND id <> ?");
$q->execute(array($user['email'], user_id()));
if ($q->fetch()) {
	$errors[] = t("This e-mail address is already being used by another account for password login.");
}

if (!$errors) {
	$q = db()->prepare("UPDATE users SET password_hash=?, password_last_changed=NOW() WHERE id=?");
	$password_hash = md5(get_site_config('password_salt') . $password);
	$q->execute(array($password_hash, user_id()));

	$messages[] = t("Updated password.");

	$name = $user['name'] ? $user['name'] : $user['email'];
	$email = $user['email'];
	send_user_email($user, $user['password_hash'] ? "password_changed" : "password_added", array(
		"email" => $email,
		"name" => $name,
	));

}

set_temporary_messages($messages);
set_temporary_errors($errors);

redirect(url_for('user#user_password'));
