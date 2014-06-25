<?php

require(__DIR__ . "/../inc/global.php");
require_login();

$user = get_user(user_id());

$messages = array();
$errors = array();

$confirm = require_post("confirm", false);

if ($password && (strlen($password) < 6 || strlen($password) > 255)) {
	$errors[] = t("You did not select the confirmation checkbox.");
}

if (!$errors) {
	$q = db()->prepare("UPDATE users SET password_hash=NULL, password_last_changed=NOW() WHERE id=?");
	$q->execute(array(user_id()));

	$messages[] = t("Removed password.");

	$name = $user['name'] ? $user['name'] : $user['email'];
	$email = $user['email'];
	send_user_email($user, "password_removed", array(
		"email" => $email,
		"name" => $name,
		"url" => absolute_url(url_for("user#user_openid")),
	));

}

set_temporary_messages($messages);
set_temporary_errors($errors);

redirect(url_for('user#user_password'));
