<?php

require(__DIR__ . "/../inc/global.php");

// POST overrides GET
$destination = require_post("destination", require_get("destination", false));
$autologin = require_post("autologin", require_get("autologin", true));
$use_password = require_post("use_password", require_get("use_password", false));

$email = $use_password ? trim(require_post("email", require_get("email", false))) : false;
$password = $use_password ? require_post("password", require_get("password", false)) : false;
if ($password && !is_string($password)) {
	throw new Exception(t("Invalid password parameter"));
}
$error = "";
$logout = require_post("logout", require_get("logout", false));
$openid = $use_password ? false : require_post("openid", require_get("openid", require_post("openid_manual", require_get("openid_manual", false))));
if ($openid && !is_string($openid)) {
	throw new Exception(t("Invalid openid parameter"));
}

$messages = array();
$errors = array();

// try logging in?
try {
	if ($openid && $password) {
		// but you can add OpenID identities later
		throw new EscapedException(t("You cannot use both OpenID and password at login."));
	}

	if ($logout) {
		$_SESSION["user_id"] = "";
		$_SESSION["user_key"] = "";
		unset($_SESSION["user_id"]);
		unset($_SESSION["user_key"]);

		// disable autologin for this session
		$_SESSION["autologin_disable"] = 1;

		$messages[] = t("Successfully logged out. You may login again here.");

	} elseif ($openid && !require_get("pause", false)) {
		if (!is_valid_url($openid)) {
			throw new EscapedException(t("That is not a valid OpenID identity."));
		}

		require(__DIR__ . "/../vendor/lightopenid/lightopenid/openid.php");
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
			throw new EscapedException(t("User has cancelled authentication."));

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
					throw new EscapedException(t("No account for the OpenID identity ':identity' were found. You may need to :signup.",
							array(
								':identity' => htmlspecialchars($light->identity),
								':signup' => link_to(url_for('signup', array('openid' => $openid)), t("signup first")),
							)));
				}

				$user = get_user($identity['user_id']);
				if (!$user) {
					throw new EscapedException(t("No user ID :id exists.", array(':id' => htmlspecialchars($identity['user_id']))));
				}

			} else {
				throw new EscapedException(t("OpenID validation was not successful: :cause", array(':cause' => $light->validate_error ? htmlspecialchars($light->validate_error) : t("Please try again."))));
			}

		}

		complete_login($user, $autologin);

		// redirect
		if (!$destination) {
			$destination = url_for(get_site_config('default_login'));
		}

		set_temporary_messages($messages);
		set_temporary_errors($errors);
		// possible injection here... strip all protocol information to prevent redirection to external site
		$destination = str_replace('#[a-z]+://#im', '', $destination);
		redirect($destination);

	} elseif ($email && $password && !require_get("pause", false)) {

		$password_hash = md5(get_site_config('password_salt') . $password);
		$q = db()->prepare("SELECT * FROM users WHERE email=? AND password_hash=? LIMIT 1");
		$q->execute(array($email, $password_hash));

		$user = $q->fetch();
		if (!$user) {
			throw new EscapedException(t("Invalid username or password."));
		}

		complete_login($user, $autologin);

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
	$errors[] = t("You need to be logged in as an administrator to do that.");
}
if ($destination && !require_get("pause", false)) {
	$errors[] = t("You need to be logged in to proceed.");
}

require(__DIR__ . "/../layout/templates.php");
page_header(t("Login"), "page_login", array('js' => 'auth'));

?>

<?php require_template("login"); ?>

<div class="authentication-form">
<h2><?php echo ht("Login"); ?></h2>

<form action="<?php echo htmlspecialchars(absolute_url(url_for('login'))); ?>" method="post">
<table class="login_form">
	<tr class="signup-with login-with-openid"<?php echo $use_password ? " style=\"display:none;\"" : ""; ?>>
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
			<button id="openid" class="openid"><span class="openid openid_manual"><?php echo ht("OpenID..."); ?></span></button>

			<div id="openid_expand" style="<?php echo require_post("submit", "") == "Login" ? "" : "display:none;"; ?>">
				<table>
				<tr>
					<th><?php echo ht("OpenID URL:"); ?></th>
					<td>
						<input type="text" name="openid_manual" class="openid" id="openid_manual" size="40" value="<?php echo htmlspecialchars($openid); ?>" maxlength="255">
						<input type="submit" name="submit" value="<?php echo ht("Login"); ?>" id="openid_manual_submit">
						<?php if ($destination) { ?>
						<input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination); ?>">
						<?php } ?>
					</td>
				</tr>
				</table>
			</div>

			<hr>
			<a class="password-openid-switch" href="<?php echo htmlspecialchars(url_for('signup', array('use_password' => true))); ?>"><?php echo ht("Use a password instead"); ?></a>
		</td>
	</tr>
	<tr class="login-with-password"<?php echo !$use_password ? " style=\"display:none;\"" : ""; ?>>
		<th><label for="password"><?php echo ht("E-mail:"); ?></label></th>
		<td>
			<input type="text" id="email" name="email" size="48" value="<?php echo htmlspecialchars($email); ?>" maxlength="255">
		</td>
	</tr>
	<tr class="login-with-password"<?php echo !$use_password ? " style=\"display:none;\"" : ""; ?>>
		<th><label for="password"><?php echo ht("Password:"); ?></label></th>
		<td>
			<input type="password" id="password" name="password" size="32" value="" maxlength="255">
			<br>
			<input type="submit" name="submit" value="<?php echo ht("Login"); ?>" id="password_manual_submit">

			<a class="forgotten-password" href="<?php echo htmlspecialchars(url_for('password', array('email' => $email))); ?>"><?php echo ht("Forgotten password?"); ?></a>

			<hr>
			<a class="password-openid-switch" href="<?php echo htmlspecialchars(url_for('signup', array('use_password' => false))); ?>"><?php echo ht("Use OpenID instead"); ?></a>

		</td>
	</tr>
	<tr class="autologin">
		<th></th>
		<td><label><input type="checkbox" name="autologin" value="1"<?php echo $autologin ? " checked" : ""; ?>> <?php echo ht("Log in automatically"); ?></label></td>
	</tr>
</table>
<input type="hidden" name="use_password" id="use_password" value="<?php echo $use_password ? 1 : 0; ?>">
</form>
</div>

<?php
page_footer();
