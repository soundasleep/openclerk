<?php

/**
 * Allows users to add additional OpenID locations for their account.
 */

require_login();

// POST overrides GET
$openid = require_post("openid", require_get("openid", require_post("openid_manual", require_get("openid_manual", false))));
if ($openid && !is_string($openid)) {
  throw new Exception("Invalid openid parameter");
}

$messages = array();
$errors = array();

// try logging in?
try {
  if ($openid) {
    $user = \Users\User::getInstance(db());

    $args = array("openid" => $openid);
    $redirect = absolute_url(url_for('openid_add', $args));

    try {
      $identity = \Users\UserOpenID::addIdentity(db(), $user, $openid, $redirect);

      $messages[] = t("Added OpenID identity ':identity' to your account.", array(':identity' => htmlspecialchars($identity)));

      // redirect
      $destination = url_for('user#user_openid');

      set_temporary_messages($messages);
      set_temporary_errors($errors);
      redirect($destination);

    } catch (\Users\UserSignupException $e) {
      $errors[] = $e->getMessage();
    }

  }
} catch (Exception $e) {
  if (!($e instanceof EscapedException)) {
    $e = new EscapedException(htmlspecialchars($e->getMessage()), (int) $e->getCode() /* PDO getCode doesn't return an int */, $e);
  }
  $errors[] = $e->getMessage();
}

require(__DIR__ . "/../layout/templates.php");
page_header(t("Add OpenID Identity"), "page_openid_add", array('js' => 'auth'));

?>

<?php require_template("openid_add"); ?>

<div class="authentication-form">
<h2><?php echo ht("Add OpenID Identity"); ?></h2>

<form action="<?php echo htmlspecialchars(absolute_url(url_for('openid_add'))); ?>" method="post">
<table class="login_form">
  <tr class="signup-with">
    <th><?php echo ht("Login with:"); ?></th>
    <td>
      <input type="hidden" name="submit" value="1">

      <?php
      $openids = get_default_openid_providers();
      foreach ($openids as $key => $data) { ?>
        <button type="submit" name="openid" class="openid openid-submit" value="<?php echo htmlspecialchars($data[1]); ?>"><span class="openid <?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($data[0]); ?></span></button>
      <?php }
      ?>

      <hr>
      <button id="openid" class="openid"><span class="openid openid_manual"><?php echo ht("OpenID..."); ?></a></button>

      <div id="openid_expand" style="<?php echo require_post("submit", "") == "Login" ? "" : "display:none;"; ?>">
      <table>
      <tr>
        <th><?php echo ht("OpenID URL:"); ?></th>
        <td>
          <input type="text" name="openid_manual" class="openid" id="openid_manual" size="40" value="<?php echo htmlspecialchars($openid); ?>" maxlength="255">
          <input type="submit" name="submit" value="<?php echo ht("Login"); ?>" id="openid_manual_submit">
        </td>
      </tr>
      </table>
      </div>
    </td>
  </tr>
</table>
</form>
</div>

<?php
page_footer();
