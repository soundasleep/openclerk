<?php

require("inc/global.php");

// POST overrides GET
$destination = require_post("destination", require_get("destination", false));
$autologin = require_post("autologin", require_get("autologin", false));
$error = "";
$logout = require_post("logout", require_get("logout", false));
$openid = require_post("openid", require_get("openid", false));

// try logging in?
try {
	if ($logout) {
		$_SESSION["user_id"] = "";
		$_SESSION["user_key"] = "";
		unset($_SESSION["user_id"]);
		unset($_SESSION["user_key"]);

		// disable autologin for this session
		$_SESSION["autologin_disable"] = 1;

		$success = "Successfully logged out. You may login again here.";

	} elseif ($openid) {
		require("inc/lightopenid/openid.php");
		$light = new LightOpenID(get_openid_host());

		if (!$light->mode) {
			// we still need to authenticate

			$light->identity = $openid;
			// The following two lines request email, full name, and a nickname
			// from the provider. Remove them if you dont need that data.
			// $light->required = array('contact/email');
			// $light->optional = array('namePerson', 'namePerson/friendly');

			// we want to add the openid identity URL to the return address
			// (the return URL is also verified in validate())
			$args = array("openid" => $openid);
			if ($autologin)
				$args["autologin"] = $autologin;
			if ($destination)
				$args["destination"] = $destination;
			$light->returnUrl = absolute_url(url_for('login', $args));

			redirect($light->authUrl());

		} else if ($light->mode == 'cancel') {
			// user has cancelled
			throw new Exception("User has cancelled authentication.");

		} else {
			// authentication is complete
			if ($light->validate()) {
				// we authenticate everything against a particular identity, not what is provided by the user
				// e.g. OpenID authenticating against http://foo.livejournal.com/?param=two#hash will return
				// an identity of http://foo.livejournal.com/.

				$query = db()->prepare("SELECT * FROM users WHERE openid_identity=? LIMIT 1");
				$query->execute(array($light->identity));
				if (!($user = $query->fetch())) {
					throw new Exception("No account for the OpenID identity '" . htmlspecialchars($light->identity) . "' were found. You may need to <a href=\"" . url_for('signup', array('openid' => $openid)) . "\">signup first</a>.");
				}

			} else {
				throw new Exception("OpenID validation was not successful: " . ($light->validate_error ? $light->validate_error : "Please try again."));
			}

		}

		// update login time
		$query = db()->prepare("UPDATE users SET last_login=NOW() WHERE id=?");
		$query->execute(array($user["id"]));

		// delete old web keys
		$query = db()->prepare("DELETE FROM valid_user_keys WHERE user_id=? AND DATEDIFF(NOW(), created_at) > ?");
		$query->execute(array($user["id"], 30 /* maximum length of autologin key or web key */ ));

		// create new login key
		$user_key = sprintf("%04x%04x%04x%04x", rand(0,0xffff), rand(0,0xffff), rand(0,0xffff), rand(0,0xffff));
		$query = db()->prepare("INSERT INTO valid_user_keys SET user_id=?, user_key=?, created_at=NOW()");
		$query->execute(array($user["id"], $user_key));

		// update session data
		$_SESSION["user_id"] = $user["id"];
		$_SESSION["user_key"] = $user_key;
		$_SESSION["user_name"] = $user["name"];
		$_SESSION["autologin_disable"] = 0;
		unset($_SESSION["autologin_disable"]);

		// update autologin
		if ($autologin) {
			setcookie('autologin_id', $user["id"], time() + get_site_config("autologin_cookie_seconds"));
			setcookie('autologin_key', $user_key, time() + get_site_config("autologin_cookie_seconds"));
		} else {
			// remove any autologin
			setcookie('autologin_id', "", time() - 3600);
			setcookie('autologin_key', "", time() - 3600);
		}

		// redirect
		if (!$destination)
			$destination = url_for('status');

		// possible injection here... strip all protocol information to prevent redirection to external site
		$destination = str_replace('#[a-z]+://#im', '', $destination);
		redirect($destination);

	}
} catch (Exception $e) {
	$error = $e->getMessage();
}

if (require_get("need_admin", false)) {
	$error .= "\nYou need to be logged in as an administrator to do that.";
}
if ($destination) {
	$error .= "\nYou need to be logged in to proceed.";
}

require("layout/templates.php");
page_header("Login", "page_login");

?>
<h1>Login</h1>

<?php if (isset($error) && $error) { ?>
<div class="error"><?php echo $error; ?></div>
<?php } ?>
<?php if (isset($success) && $success) { ?>
<div class="success"><?php echo $success; ?></div>
<?php } ?>

<div class="column0">
	<h2>Login with OpenID</h2>

	<form action="<?php echo url_for('login'); ?>" method="POST" class="login">
	<table class="login_form">
	<tr>
		<th>OpenID URL</th>
		<td><input type="text" name="openid" value="<?php if ($openid) echo htmlspecialchars($openid); ?>" maxlength="255"></td>
	</tr>
	<tr>
		<th></th>
		<td><label><input type="checkbox" name="autologin"<?php if ($autologin) echo " checked"; ?>> Automatically log in</label></td>
	</tr>
	<tr>
		<td colspan="2" class="buttons">
			<?php $destination = require_get("destination", require_post("destination", false));
			if ($destination) { ?>
			<input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination); ?>">
			<?php } ?>
			<input type="submit" value="Login">
		</td>
	</tr>
	</table>
	</form>

</div>

<div class="column1">
	<h2>Login with Google Accounts</h2>

	<form action="<?php echo url_for('login'); ?>" method="POST" class="login">
	<table class="login_form">
	<tr>
		<th></th>
		<td><label><input type="checkbox" name="autologin"<?php if ($autologin) echo " checked"; ?>> Automatically log in</label></td>
	</tr>
	<tr>
		<td colspan="2" class="buttons">
			<?php $destination = require_get("destination", require_post("destination", false));
			if ($destination) { ?>
			<input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination); ?>">
			<?php } ?>
			<input type="hidden" name="openid" value="https://www.google.com/accounts/o8/id">
			<input type="submit" value="Login">
		</td>
	</tr>
	</table>
	</form>

</div>

<div style="clear:both;"></div>

<?php
page_footer();