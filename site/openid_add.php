<?php

/**
 * Allows users to add additional OpenID locations for their account.
 */

require(__DIR__ . "/../inc/global.php");
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
		if (!is_valid_url($openid)) {
			throw new EscapedException("That is not a valid OpenID identity.");
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
			$light->returnUrl = absolute_url(url_for('openid_add', $args));

			redirect($light->authUrl());

		} else if ($light->mode == 'cancel') {
			// user has cancelled
			throw new EscapedException("User has cancelled authentication.");

		} else {
			// throws a BlockedException if this IP has requested this too many times recently
			check_heavy_request();

			// authentication is complete
			if ($light->validate()) {

				// check that we don't already have this identity
				$query = db()->prepare("SELECT * FROM openid_identities WHERE url=? LIMIT 1");
				$query->execute(array($light->identity));
				if ($user = $query->fetch()) {
					// a user already exists
					throw new EscapedException("An account for the OpenID identity '" . htmlspecialchars($light->identity) . "' already exists. Did you mean to <a href=\"" . url_for('login', array('openid' => $openid)) . "\">login instead</a>?");
				}

				// we have successfully authenticated; add this as a new OpenID identity for this user
				$q = db()->prepare("INSERT INTO openid_identities SET user_id=?,url=?");
				$q->execute(array(user_id(), $light->identity));

				$messages[] = "Added OpenID identity '" . htmlspecialchars($light->identity) . "' to your account.";

				// redirect
				$destination = url_for('user#user_openid');

				set_temporary_messages($messages);
				set_temporary_errors($errors);
				redirect($destination);

			} else {
				throw new EscapedException("OpenID validation was not successful: " . ($light->validate_error ? htmlspecialchars($light->validate_error) : "Please try again."));
			}

		}

	}
} catch (Exception $e) {
	if (!($e instanceof EscapedException)) {
		$e = new EscapedException(htmlspecialchars($e->getMessage()), (int) $e->getCode() /* PDO getCode doesn't return an int */, $e);
	}
	$errors[] = $e->getMessage();
}

require(__DIR__ . "/../layout/templates.php");
page_header("Add OpenID Identity", "page_openid_add", array('jquery' => true, 'js' => 'auth'));

?>

<?php require_template("openid_add"); ?>

<div class="authentication-form">
<h2>Add OpenID Identity</h2>

<form action="<?php echo htmlspecialchars(absolute_url(url_for('openid_add'))); ?>" method="post">
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
			<button id="openid" class="openid"><span class="openid openid_manual">OpenID...</a></button>

			<div id="openid_expand" style="<?php echo require_post("submit", "") == "Login" ? "" : "display:none;"; ?>">
			<table>
			<tr>
				<th>OpenID URL:</th>
				<td>
					<input type="text" name="openid_manual" class="openid" id="openid_manual" size="40" value="<?php echo htmlspecialchars($openid); ?>" maxlength="255">
					<input type="submit" name="submit" value="Login" id="openid_manual_submit">
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
