<?php

$email = trim(require_post("email", require_get("email", false)));
$hash = require_post("hash", require_get("hash", false));

$password = require_post("password", require_get("password", false));
if ($password && !is_string($password)) {
	throw new Exception(t("Invalid password parameter"));
}
$password2 = require_post("password2", require_get("password2", false));
if ($password2 && !is_string($password2)) {
	throw new Exception(t("Invalid repeated password parameter"));
}

$messages = array();
$errors = array();

if ($email && $password) {
	if (!$hash) {
		$errors[] = t("No hash specified.");
	}
	if ($password && (strlen($password) < 6 || strlen($password) > 255)) {
		$errors[] = t("Please select a password between :min-:max characters long.", array(':min' => 6, ':max' => 255));
	}
	if ($password && $password != $password2) {
		$errors[] = t("Those passwords do not match.");
	}

	// check the request hash
	$q = db()->prepare("SELECT * FROM users WHERE email=? AND ISNULL(password_hash) = 0");
	$q->execute(array($email));
	$user = $q->fetch();
	if (!$user) {
		$errors[] = t("No such user account exists.");
	} else if (!$user['last_password_reset'] || strtotime($user['last_password_reset']) < strtotime("-1 day")) {
		$errors[] = t("That account has not requested a password reset.");
	} else {
		$expected_hash = md5(get_site_config('password_reset_salt') . $email . ":" . strtotime($user['last_password_reset']));
		if ($hash != $expected_hash) {
			$errors[] = t("Invalid hash - please recheck the link in your e-mail.");
		}
	}

	if (!$errors) {
		$q = db()->prepare("UPDATE users SET password_hash=?, password_last_changed=NOW() WHERE id=?");
		$password_hash = md5(get_site_config('password_salt') . $password);
		$q->execute(array($password_hash, $user['id']));

		send_user_email($user, "password_reset_complete", array(
			"email" => $email,
			"name" => $user['name'] ? $user['name'] : $email,
		));

		$messages[] = t("Password changed; you should now immediately login with this new password.");

		set_temporary_messages($messages);
		set_temporary_errors($errors);
		redirect(url_for('login', array('email' => $email, 'use_password' => true)));

	}
}

require(__DIR__ . "/../layout/templates.php");
page_header(t("Change Password"), "page_password_reset", array('js' => 'auth'));

?>

<?php require_template("password_reset"); ?>

<div class="authentication-form">
<h2><?php echo ht("Change password"); ?></h2>

<form action="<?php echo htmlspecialchars(url_for('password_reset')); ?>" method="post">
<table class="login_form">
<tr>
	<th><?php echo ht("E-mail:"); ?></th>
	<td><?php echo htmlspecialchars($email); ?></td>
</tr>
<tr>
	<th><label for="password"><?php echo ht("Password:"); ?></label></th>
	<td>
		<input type="password" id="password" name="password" size="32" value="" maxlength="255"> <span class="required">*</span>
	</td>
</tr>
<tr>
	<th><label for="password2"><?php echo ht("Repeat:"); ?></label></th>
	<td>
		<input type="password" id="password2" name="password2" size="32" value="" maxlength="255"> <span class="required">*</span>
	</td>
</tr>
<tr>
	<td colspan="2" class="buttons">
		<input type="submit" value="<?php echo ht("Add password"); ?>">
		<input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
		<input type="hidden" name="hash" value="<?php echo htmlspecialchars($hash); ?>">
	</td>
</tr>
</table>
</form>
</div>

<?php
page_footer();
