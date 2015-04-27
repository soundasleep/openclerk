<?php

/**
 * Allows users to add additional OAuth2 locations for their account.
 * Issue #266
 */

require_login();

// POST overrides GET
$oauth2 = require_post("oauth2", require_get("oauth2", false));

$messages = array();
$errors = array();

try {
  if ($oauth2) {
    $user = \Users\User::getInstance(db());

    $args = array("oauth2" => $oauth2);
    $url = absolute_url(url_for('oauth2_add', $args));

    $provider = Users\OAuth2Providers::createProvider($oauth2, $url);

    try {
      \Users\UserOAuth2::addIdentity(db(), $user, $provider);

      $messages[] = t("Added OAuth2 identity ':identity' to your account.", array(':identity' => htmlspecialchars($provider->getKey())));

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
page_header(t("Add OAuth2 Identity"), "page_oauth2_add", array('js' => 'auth'));

?>

<?php require_template("oauth2_add"); ?>

<div class="authentication-form">
<h2><?php echo ht("Add OAuth2 Identity"); ?></h2>

<form action="<?php echo htmlspecialchars(absolute_url(url_for('oauth2_add'))); ?>" method="post">
<table class="login_form">
  <tr class="signup-with">
    <th><?php echo ht("Login with:"); ?></th>
    <td>
      <input type="hidden" name="submit" value="1">

      <?php
      $openids = get_default_oauth2_providers();
      foreach ($openids as $key => $data) { ?>
        <button type="submit" name="oauth2" class="oauth2 oauth2-submit" value="<?php echo htmlspecialchars($key); ?>"><span class="oauth2 <?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($data); ?></span></button>
      <?php }
      ?>

    </td>
  </tr>
</table>
</form>
</div>

<?php
page_footer();
