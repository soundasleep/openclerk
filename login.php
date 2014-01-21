<?php

require(__DIR__ . "/inc/global.php");

// POST overrides GET
$destination = require_post("destination", require_get("destination", false));
$autologin = require_post("autologin", require_get("autologin", true));
$error = "";
$logout = require_post("logout", require_get("logout", false));
$openid = require_post("openid", require_get("openid", require_post("openid_manual", require_get("openid_manual", false))));
if ($openid && !is_string($openid)) {
	throw new Exception("Invalid openid parameter");
}

$messages = array();
$errors = array();

// try logging in?
try {
	if ($logout) {
		$_SESSION["user_id"] = "";
		$_SESSION["user_key"] = "";
		unset($_SESSION["user_id"]);
		unset($_SESSION["user_key"]);

		// disable autologin for this session
		$_SESSION["autologin_disable"] = 1;

		$messages[] = "Successfully logged out. You may login again here.";

	} elseif ($openid && !require_get("pause", false)) {
		if (!is_valid_url($openid)) {
			throw new EscapedException("That is not a valid OpenID identity.");
		}

		require(__DIR__ . "/inc/lightopenid/openid.php");
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
			throw new EscapedException("User has cancelled authentication.");

		} else {
			// throws a BlockedException if this IP has requested this too many times recently
			check_heavy_request();

			// authentication is complete
			if ($light->validate()) {
				// we authenticate everything against a particular identity, not what is provided by the user
				// e.g. OpenID authenticating against http://foo.livejournal.com/?param=two#hash will return
				// an identity of http://foo.livejournal.com/.

				$q = db()->prepare("SELECT * FROM openid_identities WHERE url=? LIMIT 1");
				$q->execute(array($light->identity));
				if (!($identity = $q->fetch())) {
					throw new EscapedException("No account for the OpenID identity '" . htmlspecialchars($light->identity) . "' were found. You may need to <a href=\"" . url_for('signup', array('openid' => $openid)) . "\">signup first</a>.");
				}

				$user = get_user($identity['user_id']);
				if (!$user) {
					throw new EscapedException("No user ID " . htmlspecialchars($identity['user_id']) . " exists.");
				}

			} else {
				throw new EscapedException("OpenID validation was not successful: " . ($light->validate_error ? htmlspecialchars($light->validate_error) : "Please try again."));
			}

		}

		// delete old web keys
		$query = db()->prepare("DELETE FROM valid_user_keys WHERE user_id=? AND DATEDIFF(NOW(), created_at) > ?");
		$query->execute(array($user["id"], get_site_config("autologin_expire_days") /* maximum length of autologin key or web key */ ));

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

		// handle post-login
		handle_post_login();

		// redirect
		if (!$destination) {
			$destination = url_for(get_site_config('default_login'));
		}

		set_temporary_messages($messages);
		set_temporary_errors($errors);
		// possible injection here... strip all protocol information to prevent redirection to external site
		$destination = str_replace('#[a-z]+://#im', '', $destination);
		redirect($destination);

	}
} catch (Exception $e) {
	if (!($e instanceof EscapedException)) {
		$e = new EscapedException(htmlspecialchars($e->getMessage()), (int) $e->getCode() /* PDO getCode doesn't return an int */, $e);
	}
	$errors[] = $e->getMessage();
}

if (require_get("need_admin", false)) {
	$errors[] = "You need to be logged in as an administrator to do that.";
}
if ($destination && !require_get("pause", false)) {
	$errors[] = "You need to be logged in to proceed.";
}

require(__DIR__ . "/layout/templates.php");
page_header("Login", "page_login", array('jquery' => true, 'js' => 'auth'));

?>

<?php require_template("login"); ?>

<div class="authentication-form">
<h2>Login</h2>

<form action="<?php echo htmlspecialchars(absolute_url(url_for('login'))); ?>" method="post">
<table class="login_form">
	<tr class="signup-with">
		<th>Login with:</th>
		<td>
			<input type="hidden" name="submit" value="1">

			<?php
			$openids = get_default_openid_providers();
			foreach ($openids as $key => $data) { ?>
				<button type="submit" name="openid" class="openid openid-submit" value="<?php echo htmlspecialchars($data[1]); ?>"><span class="openid <?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($data[0]); ?></span></button>
			<?php }
			?>

			<hr>
			<button id="openid" class="openid"><span class="openid openid_manual">OpenID...</span></button>

			<div id="openid_expand" style="<?php echo require_post("submit", "") == "Login" ? "" : "display:none;"; ?>">
			<table>
			<tr>
				<th>OpenID URL:</th>
				<td>
					<input type="text" name="openid_manual" class="openid" id="openid_manual" size="40" value="<?php echo htmlspecialchars($openid); ?>" maxlength="255">
					<input type="submit" name="submit" value="Login" id="openid_manual_submit">
					<?php if ($destination) { ?>
					<input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination); ?>">
					<?php } ?>
				</td>
			</tr>
			</table>
			</div>
		</td>
	</tr>
	<tr>
		<th></th>
		<td><label><input type="checkbox" name="autologin" value="1"<?php echo $autologin ? " checked" : ""; ?>> Log in automatically</label></td>
	</tr>
</table>
</form>
</div>

<?php
page_footer();
