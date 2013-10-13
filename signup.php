<?php

require(__DIR__ . "/inc/global.php");
require(__DIR__ . "/inc/countries.php");

// only permit POST for some variables
$email = require_post("email", require_get("email", false));
$name = require_post("name", require_get("name", false));
$agree = require_post("agree", require_get("agree", false));
$submit = require_post("submit", require_get("submit", false));
$subscribe = require_post("subscribe", require_get("subscribe", $submit ? false : true));
$openid = require_post("openid", require_get("openid", false));
$country = require_post("country", require_get("country", false));

$messages = array();
$errors = array();

if ($openid && $submit) {
	if (!$country || strlen($country) != 2) {
		$errors[] = "You need to select your country.";
	}
	if ($email && !is_valid_email($email)) {
		$errors[] = "That is not a valid e-mail address.";
	}
	if (!$agree) {
		$errors[] = "You need to agree to the terms of service.";
	}
	if ($subscribe && !$email) {
		$errors[] = "To subscribe to site announcements, you must provide an e-mail address.";
	}

	if (!$errors) {
		try {
			// to sign up with OpenID, we must first authenticate to see if the identity already exists
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
				$args = array('openid' => $openid, 'submit' => 1, 'name' => $name, 'email' => $email, 'country' => $country, 'agree' => $agree, 'subscribe' => $subscribe);
				// persist session ID (to keep referer) if the user hasn't saved cookie
				if (session_name()) {
					$args[session_name()] = session_id();
				}
				$light->returnUrl = absolute_url(url_for('signup', $args));

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
					// print_r($light->getAttributes());

					$query = db()->prepare("SELECT * FROM users WHERE openid_identity=? LIMIT 1");
					$query->execute(array($light->identity));
					if ($user = $query->fetch()) {
						// a user already exists
						throw new EscapedException("An account for the OpenID identity '" . htmlspecialchars($light->identity) . "' already exists. Did you mean to <a href=\"" . url_for('login', array('openid' => $openid)) . "\">login instead</a>?");
					}

				} else {
					throw new EscapedException("OpenID validation was not successful: " . ($light->validate_error ? htmlspecialchars($light->validate_error) : "Please try again."));
				}

			}

			// we can now proceed with creating a new user account
			$query = db()->prepare("INSERT INTO users SET
				name=:name, email=:email, openid_identity=:identity, country=:country, user_ip=:ip, referer=:referer, subscribe_announcements=:subscribe, created_at=NOW(), updated_at=NOW()");
			$user = array(
				"name" => $name,
				"email" => $email,
				"identity" => $light->identity,
				"country" => $country,
				"ip" => user_ip(),
				"referer" => isset($_SESSION['referer']) ? substr($_SESSION['referer'], 0, 250) : NULL,
				"subscribe" => $subscribe ? 1 : 0,
			);
			$query->execute($user);
			$user['id'] = db()->lastInsertId();

			if ($subscribe) {
				$q = db()->prepare("INSERT INTO pending_subscriptions SET user_id=?,created_at=NOW(),is_subscribe=1");
				$q->execute(array($user['id']));
				$messages[] = "You will be added manually to the <a href=\"http://groups.google.com/group/" . htmlspecialchars(get_site_config('google_groups_announce')) . "\" target=\"_blank\">Announcements Mailing List</a> soon.";
			}

			// try sending email
			if ($email) {
				send_email($email, $email, "signup", array(
					"email" => $email,
					"name" => $name ? $name : $email,
					"announcements" => "http://groups.google.com/group/" . htmlspecialchars(get_site_config('google_groups_announce')),
					"url" => absolute_url(url_for("unsubscribe", array('email' => $email, 'hash' => md5(get_site_config('unsubscribe_salt') . $email)))),
				));
			}

			// create default summary pages and cryptocurrencies and graphs contents
			reset_user_settings($user['id']);

			// success!
			$messages[] = "New account creation successful; you may now login.";

			// redirect
			set_temporary_messages($messages);
			// 'pause' parameter is set to prevent trying to login straight away, which will fail because of heavy requests
			redirect(url_for('login', array('pause' => true, 'openid' => $openid, 'destination' => url_for(get_site_config('premium_welcome') ? "welcome" : get_site_config('signup_login')))));

		} catch (Exception $e) {
			if (!($e instanceof EscapedException)) {
				$e = new EscapedException(htmlspecialchars($e->getMessage()), (int) $e->getCode() /* PDO getCode doesn't return an int */, $e);
			}
			$errors[] = $e->getMessage();
		}
	}
}

require(__DIR__ . "/layout/templates.php");
page_header("Signup", "page_signup", array('jquery' => true, 'js' => 'auth'));

?>

<?php require_template("signup"); ?>

<div class="authentication-form">
<h2>Signup</h2>

<form action="<?php echo url_for('signup'); ?>" method="post">
<table class="login_form">
	<tr>
		<th><label for="name">Name:</label></th>
		<td><input type="text" id="name" name="name" size="32" value="<?php echo htmlspecialchars($name); ?>" maxlength="255"></td>
	</tr>
	<tr>
		<th><label for="country">Country:</label></th>
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
		<th><label for="email">Email:</label></th>
		<td><input type="text" id="email" name="email" size="48" value="<?php echo htmlspecialchars($email); ?>" maxlength="255"></td>
	</tr>
	<tr>
		<th></th>
		<td><label><input type="checkbox" name="subscribe" value="1"<?php echo $subscribe ? " checked" : ""; ?>> Subscribe to site announcements</label></td>
	</tr>
	<tr>
		<th></th>
		<td><label><input type="checkbox" name="agree" value="1"<?php echo $agree ? " checked" : ""; ?>> I agree to the <a href="<?php echo htmlspecialchars(url_for('terms')); ?>" target="_blank">Terms of Service</a></label> <span class="required">*</span></td>
	</tr>
	<tr>
		<td colspan="2" class="hr"><hr></td>
	</tr>
	<tr class="signup-with">
		<th>Signup with:</th>
		<td>
			<input type="hidden" name="submit" value="1">

			<?php
			$openids = get_default_openid_providers();
			foreach ($openids as $key => $data) { ?>
				<button type="submit" name="openid" class="openid openid-submit" value="<?php echo htmlspecialchars($data[1]); ?>"><span class="openid <?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($data[0]); ?></span></button>
			<?php }
			?>

			<br>
			<button id="openid" class="openid"><span class="openid openid_manual">OpenID...</a></button>

			<div id="openid_expand" style="<?php echo require_post("submit", "") == "Signup" ? "" : "display:none;"; ?>">
			<table>
				<th>OpenID URL:</th>
				<td>
					<input type="text" name="openid" class="openid" size="40" value="<?php echo htmlspecialchars($openid); ?>" maxlength="255">
					<input type="submit" name="submit" value="Signup">
				</td>
			</table>
			</div>
		</td>
	</tr>
</table>
</form>
</div>

<?php
page_footer();