<?php

define('USE_MASTER_DB', true);

// only permit POST for some variables
$autologin = require_post("autologin", require_get("autologin", true));
$use_password = require_post("use_password", require_get("use_password", false));
$email = trim(require_post("email", require_get("email", require_session("signup_email", false))));

$password = $use_password ? require_post("password", require_get("password", false)) : false;
if ($password && !is_string($password)) {
  throw new Exception(t("Invalid password parameter"));
}
$password2 = $use_password ? require_post("password2", require_get("password2", false)) : false;
if ($password2 && !is_string($password2)) {
  throw new Exception(t("Invalid repeated password parameter"));
}
$name = require_post("name", require_get("name", require_session("signup_name", false)));
$agree = require_post("agree", require_get("agree", require_session("signup_agree", false)));
$openid = require_post("openid", require_get("openid", require_post("openid_manual", require_get("openid_manual", false))));
$oauth2 = require_post("oauth2", require_get("oauth2", false));
if ($openid && !is_string($openid)) {
  throw new Exception(t("Invalid OpenID parameter"));
}
$subscribe = require_post("subscribe", require_get("subscribe", require_session("signup_subscribe", $openid || $oauth2 ? false : true)));
$country = require_post("country", require_get("country", require_session("signup_country", false)));

$messages = array();
$errors = array();

unset($_SESSION['signup_name']);
unset($_SESSION['signup_email']);
unset($_SESSION['signup_country']);
unset($_SESSION['signup_agree']);
unset($_SESSION['signup_subscribe']);

if ($openid || $oauth2 || $password) {
  if (!$country || strlen($country) != 2) {
    $errors[] = t("You need to select your country.");
  }
  if ($email && !is_valid_email($email)) {
    $errors[] = t("That is not a valid e-mail address.");
  }
  if (!$agree) {
    $errors[] = t("You need to agree to the terms of service.");
  }
  if ($subscribe && !$email) {
    $errors[] = t("To subscribe to site announcements, you must provide an e-mail address.");
  }
  if ($password && !$email) {
    $errors[] = t("You need to select your e-mail address in order to use password login.");
  }
  if ($password && (strlen($password) < 6 || strlen($password) > 255)) {
    $errors[] = t("Please select a password between :min-:max characters long.", array(':min' => 6, ':max' => 255));
  }
  if ($password && $password != $password2) {
    $errors[] = t("Those passwords do not match.");
  }
  if ($openid && $password) {
    // but you can add OpenID identities later
    $errors[] = t("You cannot use both OpenID and password at signup.");
  }

  if (!$errors) {
    try {
      $user = false;

      try {
        if ($oauth2) {
          // try OAuth2 signup

          // save parameters for callback later
          $_SESSION['signup_name'] = $name;
          $_SESSION['signup_email'] = $email;
          $_SESSION['signup_country'] = $country;
          $_SESSION['signup_agree'] = $agree;
          $_SESSION['signup_subscribe'] = $subscribe;

          $args = array('oauth2' => $oauth2);
          $url = absolute_url(url_for('signup', $args));

          $provider = Users\OAuth2Providers::createProvider($oauth2, $url);
          $user = Users\UserOAuth2::trySignup(db(), $provider, $url);

        } else if ($openid) {
          // try OpenID signup

          // we want to add the openid identity URL to the return address
          // (the return URL is also verified in validate())
          $args = array('openid' => $openid, 'submit' => 1, 'name' => $name, 'email' => $email, 'country' => $country, 'agree' => $agree, 'subscribe' => $subscribe);
          // persist session ID (to keep referer) if the user hasn't saved cookie
          if (session_name()) {
            $args[session_name()] = session_id();
          }
          $url = absolute_url(url_for('signup', $args));

          $user = Users\UserOpenID::trySignup(db(), $email /* may be null */, $openid, $url);

        } else if ($email && $password) {
          // try email/password signup

          $user = Users\UserPassword::trySignup(db(), $email, $password);

        }
      } catch (\Users\UserAlreadyExistsException $e) {
        $errors[] = $e->getMessage() . " " . t("Did you mean to :login?",
            array(':login' => link_to(url_for('login', array('use_password' => true, 'email' => $email, 'openid' => $openid)), t("login instead")),
          ));
      } catch (\Users\UserSignupException $e) {
        $errors[] = $e->getMessage();
      } catch (\Users\UserAuthenticationException $e) {
        $errors[] = $e->getMessage();
      }

      if ($user && !$errors) {

        $q = db()->prepare("INSERT INTO user_properties SET
          id=:id,
          name=:name, email=:email, country=:country, user_ip=:ip, referer=:referer, subscribe_announcements=:subscribe, created_at=NOW(), updated_at=NOW()");
        $user = array(
          "id" => $user->getId(),
          "name" => $name,
          "email" => $email,
          "country" => $country,
          "ip" => user_ip(),
          "referer" => isset($_SESSION['referer']) ? substr($_SESSION['referer'], 0, 250) : NULL,
          "subscribe" => $subscribe ? 1 : 0,
        );
        $q->execute($user);

        if ($subscribe) {
          $q = db()->prepare("INSERT INTO pending_subscriptions SET user_id=?,created_at=NOW(),is_subscribe=1");
          $q->execute(array($user['id']));
          $messages[] = t("You will be added manually to the :mailing_list soon.",
            array(
              ':mailing_list' => "<a href=\"http://groups.google.com/group/" . htmlspecialchars(get_site_config('google_groups_announce')) . "\" target=\"_blank\">" . t("Announcements Mailing List") . "</a>",
            ));
        }

        // try sending email
        if ($email) {
          send_user_email($user, "signup", array(
            "email" => $email,
            "name" => $name ? $name : $email,
            "announcements" => "http://groups.google.com/group/" . htmlspecialchars(get_site_config('google_groups_announce')),
            "url" => absolute_url(url_for("unsubscribe", array('email' => $email, 'hash' => md5(get_site_config('unsubscribe_salt') . $email)))),
            "wizard_currencies" => absolute_url(url_for("wizard_currencies")),
            "wizard_addresses" => absolute_url(url_for("wizard_accounts_addresses")),
            "wizard_accounts" => absolute_url(url_for("wizard_accounts")),
            "wizard_notifications" =>  absolute_url(url_for("wizard_notifications")),
            "reports" => absolute_url(url_for("profile")),
            "premium" =>  absolute_url(url_for("premium")),
          ));
        }

        // create default summary pages and cryptocurrencies and graphs contents
        reset_user_settings($user['id']);

        // success!
        // issue #62: rather than requiring another step to login, just log the user in now.
        \Users\User::forceLogin(db(), $user['id']);

        complete_login($user, $autologin);

        $messages[] = t("New account creation successful.");

        // redirect
        set_temporary_messages($messages);
        redirect(url_for(get_site_config('premium_welcome') ? "welcome" : get_site_config('signup_login'), array("pause" => true)));
      }

    } catch (Exception $e) {
      if (!($e instanceof EscapedException)) {
        $e = new EscapedException(htmlspecialchars($e->getMessage()), (int) $e->getCode() /* PDO getCode doesn't return an int */, $e);
      }
      $errors[] = $e->getMessage();
    }
  }
}

require(__DIR__ . "/../layout/templates.php");
page_header(t("Signup"), "page_signup", array('js' => 'auth'));

?>

<?php require_template("signup"); ?>

<div class="authentication-form">
<h2><?php echo ht("Signup"); ?></h2>

<form action="<?php echo htmlspecialchars(absolute_url(url_for('signup'))); ?>" method="post">
<table class="login_form">
  <tr>
    <th><label for="name"><?php echo ht("Name:"); ?></label></th>
    <td><input type="text" id="name" name="name" size="32" value="<?php echo htmlspecialchars($name); ?>" maxlength="255"></td>
  </tr>
  <tr>
    <th><label for="country"><?php echo ht("Country:"); ?></label></th>
    <td><select name="country" class="country" id="country">
      <option></option>
      <?php
        foreach (get_country_iso() as $key => $value) {
          echo "<option value=\"" . htmlspecialchars($key) . "\"" . ($country == $key ? " selected" : "") . ">" . htmlspecialchars($value) . "</option>\n";
        }
      ?>
    </select> <span class="required">*</span>
  </tr>
  <tr>
    <th><label for="email"><?php echo ht("E-mail:"); ?></label></th>
    <td><input type="text" id="email" name="email" size="48" value="<?php echo htmlspecialchars($email); ?>" maxlength="255"> <span class="required email-required"<?php echo !$use_password ? " style=\"display:none;\"" : ""; ?>>*</span></td>
  </tr>
  <tr>
    <th></th>
    <td><label><input type="checkbox" name="subscribe" value="1"<?php echo $subscribe ? " checked" : ""; ?>> <?php echo ht("Subscribe to site announcements"); ?></label></td>
  </tr>
  <tr>
    <th></th>
    <td><label><input type="checkbox" name="autologin" value="1"<?php echo $autologin ? " checked" : ""; ?>> <?php echo ht("Log in automatically"); ?></label></td>
  </tr>
  <tr>
    <th></th>
    <td><label><input type="checkbox" name="agree" value="1"<?php echo $agree ? " checked" : ""; ?>> <?php echo t("I agree to the :terms", array(':terms' => '<a href="' . htmlspecialchars(url_for('terms')) . '" target="_blank">' . ht("Terms of Service") . "</a>")); ?></label> <span class="required">*</span></td>
  </tr>
  <tr>
    <td colspan="2" class="hr"><hr></td>
  </tr>
  <tr class="signup-with login-with-openid"<?php echo $use_password ? " style=\"display:none;\"" : ""; ?>>
    <th><?php echo ht("Signup with:"); ?></th>
    <td>
      <input type="hidden" name="submit" value="1">

      <?php
      $openids = get_default_oauth2_providers();
      foreach ($openids as $key => $data) { ?>
        <button type="submit" name="oauth2" class="oauth2 oauth2-submit" value="<?php echo htmlspecialchars($key); ?>"><span class="oauth2 <?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($data); ?></span></button>
      <?php }
      ?>

      <hr>

      <?php
      $openids = get_default_openid_providers();
      foreach ($openids as $key => $data) { ?>
        <button type="submit" name="openid" class="openid openid-submit" value="<?php echo htmlspecialchars($data[1]); ?>"><span class="openid <?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($data[0]); ?></span></button>
      <?php }
      ?>

      <hr>
      <button id="openid" class="openid"><span class="openid openid_manual"><?php echo ht("OpenID..."); ?></span></button>

      <div id="openid_expand" style="<?php echo require_post("submit", "") == "Signup" ? "" : "display:none;"; ?>">
        <table>
        <tr>
          <th><?php echo ht("OpenID URL:"); ?></th>
          <td>
            <input type="text" name="openid_manual" class="openid" id="openid_manual" size="40" value="<?php echo htmlspecialchars($openid); ?>" maxlength="255">
            <input type="submit" name="submit" value="<?php echo ht("Signup"); ?>" id="openid_manual_submit">
          </td>
        </tr>
        </table>
      </div>

      <hr>
      <a class="password-openid-switch" href="<?php echo htmlspecialchars(url_for('signup', array('use_password' => true))); ?>"><?php echo ht("Use a password instead"); ?></a>

    </td>
  </tr>
  <tr class="login-with-password"<?php echo !$use_password ? " style=\"display:none;\"" : ""; ?>>
    <th><label for="password"><?php echo ht("Password:"); ?></label></th>
    <td>
      <input type="password" id="password" name="password" size="32" value="" maxlength="255"> <span class="required">*</span>
    </td>
  </tr>
  <tr class="login-with-password"<?php echo !$use_password ? " style=\"display:none;\"" : ""; ?>>
    <th><label for="password2"><?php echo ht("Repeat:"); ?></label></th>
    <td>
      <input type="password" id="password2" name="password2" size="32" value="" maxlength="255"> <span class="required">*</span>
    </td>
  </tr>
  <tr class="login-with-password"<?php echo !$use_password ? " style=\"display:none;\"" : ""; ?>>
    <th></th>
    <td>
      <input type="submit" name="submit" value="<?php echo ht("Signup"); ?>" id="password_manual_submit">

      <hr>
      <a class="password-openid-switch" href="<?php echo htmlspecialchars(url_for('signup', array('use_password' => false))); ?>"><?php echo ht("Use OpenID instead"); ?></a>

      <div class="tip">
        <?php echo t(":openid is often much more secure than using an e-mail and password. If you do use a password, please
        make sure you do not use the same password on other cryptocurrency sites.",
          array(
            ':openid' => '<a class="password-openid-switch" href="' . htmlspecialchars(url_for('signup', array('use_password' => false))) . '">' . ht("OpenID login") . '</a>',
          ));
        ?>
      </div>

    </td>
  </tr>
</table>
<input type="hidden" name="use_password" id="use_password" value="<?php echo $use_password ? 1 : 0; ?>">
</form>
</div>

<?php
page_footer();
