<?php

$email = trim(require_post("email", require_get("email", false)));
$confirm = require_post("confirm", false);

$messages = array();
$errors = array();

if ($email && $confirm) {
  try {
    // throws a BlockedException if this IP has requested this too many times recently
    check_heavy_request();
  } catch (Exception $e) {
    $errors[] = $e->getMessage();
  }

  if (!$errors) {
    $q = db()->prepare("SELECT * FROM user_properties WHERE email=? AND ISNULL(password_hash) = 0");
    $q->execute(array($email));

    if ($user = $q->fetch()) {
      $q = db()->prepare("UPDATE user_properties SET last_password_reset=NOW() WHERE id=?");
      $q->execute(array($user['id']));

      $user = get_user($user['id']);
      $hash = md5(get_site_config('password_reset_salt') . $email . ":" . strtotime($user['last_password_reset']));

      send_user_email($user, "password_reset", array(
        "email" => $email,
        "name" => $user['name'] ? $user['name'] : $email,
        "ip" => user_ip(),
        "url" => absolute_url(url_for("password_reset", array('email' => $email, 'hash' => $hash))),
      ));

      $messages[] = t("Further instructions to change your password have been sent to your e-mail address :email.", array(':email' => htmlspecialchars($email)));
    } else {
      $errors[] = t("No such user account exists.");
    }
  }
}

require(__DIR__ . "/../layout/templates.php");
page_header(t("Reset Password"), "page_password", array('js' => 'auth'));

?>

<?php require_template("password"); ?>

<div class="authentication-form">
<h2><?php echo ht("Reset password"); ?></h2>

<form action="<?php echo htmlspecialchars(absolute_url(url_for('password'))); ?>" method="post">
<table class="login_form">
  <tr>
    <th><label for="email"><?php echo ht("E-mail:"); ?></label></th>
    <td><input type="text" id="email" name="email" size="48" value="<?php echo htmlspecialchars($email); ?>" maxlength="255"></td>
  </tr>
  <tr>
    <th></th>
    <td>
      <input type="submit" name="submit" value="<?php echo ht("Reset Password"); ?>" id="password_manual_submit">
    </td>
  </tr>
</table>
<input type="hidden" name="confirm" value="1">
</form>
</div>

<?php
page_footer();
