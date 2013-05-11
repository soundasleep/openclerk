<?php

require("inc/global.php");

// POST overrides GET
$destination = require_post("destination", require_get("destination", false));
$autologin = require_post("autologin", require_get("autologin", false));
$error = "";
$logout = require_post("logout", require_get("logout", false));
$openid = require_post("openid", require_get("openid", false));

$messages = array();
$errors = array();

class EscapedException extends Exception { }

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
			throw new EscapedException("User has cancelled authentication.");

		} else {
			// authentication is complete
			if ($light->validate()) {
				// we authenticate everything against a particular identity, not what is provided by the user
				// e.g. OpenID authenticating against http://foo.livejournal.com/?param=two#hash will return
				// an identity of http://foo.livejournal.com/.

				$query = db()->prepare("SELECT * FROM users WHERE openid_identity=? LIMIT 1");
				$query->execute(array($light->identity));
				if (!($user = $query->fetch())) {
					throw new EscapedException("No account for the OpenID identity '" . htmlspecialchars($light->identity) . "' were found. You may need to <a href=\"" . url_for('signup', array('openid' => $openid)) . "\">signup first</a>.");
				}

			} else {
				throw new EscapedException("OpenID validation was not successful: " . ($light->validate_error ? htmlspecialchars($light->validate_error) : "Please try again."));
			}

		}

		// update login time
		$query = db()->prepare("UPDATE users SET updated_at=NOW(),last_login=NOW() WHERE id=?");
		$query->execute(array($user["id"]));

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

		// redirect
		if (!$destination)
			$destination = url_for(get_site_config('default_login'));

		// possible injection here... strip all protocol information to prevent redirection to external site
		$destination = str_replace('#[a-z]+://#im', '', $destination);
		redirect($destination);

	}
} catch (Exception $e) {
	if (!($e instanceof EscapedException)) {
		$e = new EscapedException(htmlspecialchars($e->getMessage()), $e->getCode(), $e);
	}
	$errors[] = $e->getMessage();
}

if (require_get("need_admin", false)) {
	$errors[] = "You need to be logged in as an administrator to do that.";
}
if ($destination) {
	$errors[] = "You need to be logged in to proceed.";
}

require("layout/templates.php");
page_header("Login", "page_login", array('jquery' => true, 'common_js' => true));

?>
<h1>Login</h1>

<?php require_template("login"); ?>

<div class="columns2">
<div class="column">

<div class="tabs" id="tabs_login1">
	<ul class="tab_list">
		<li id="tab_login1_openid">OpenID</li>
	</ul>
	<ul class="tab_groups">
		<li id="tab_login1_openid_tab">

	<h2>Login with OpenID</h2>

	<form action="<?php echo url_for('login'); ?>" method="POST" class="login">
	<table class="login_form">
	<tr>
		<th>OpenID URL</th>
		<td><input type="text" name="openid" value="<?php if ($openid) echo htmlspecialchars($openid); ?>" class="openid_url" maxlength="255"></td>
	</tr>
	<tr>
		<th></th>
		<td><label><input type="checkbox" name="autologin" checked> Automatically log in</label></td>
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

		</li>
	</ul>
</div>

</div>

<div class="column">

<?php $openid = get_default_openid_providers(); ?>
<div class="tabs" id="tabs_login">
	<ul class="tab_list">
		<?php /* each <li> must not have any whitespace between them otherwise whitespace will appear when rendered */ ?>
		<?php foreach ($openid as $key => $data) {
			echo "<li id=\"tab_login_$key\">" . htmlspecialchars($data[0]) . "</li>";
		} ?>
	</ul>

	<ul class="tab_groups">
	<?php foreach ($openid as $key => $data) { ?>
		<li id="tab_login_<?php echo $key; ?>_tab">

        <h2>Login with <?php echo htmlspecialchars($data[0]); ?></h2>

        <form action="<?php echo url_for('login'); ?>" method="POST">
        <table class="login_form">
		<tr>
			<td><label><input type="checkbox" name="autologin"<?php if ($autologin) echo " checked"; ?>> Automatically log in</label></td>
		</tr>
		<tr>
			<td class="buttons">
				<input type="hidden" name="openid" value="<?php echo htmlspecialchars($data[1]); ?>">
				<input type="submit" value="Login">
			</td>
		</tr>
        </table>
        </form>
        </li>
    <?php } ?>
    </ul>
</div>

</div>
</div>

<div style="clear:both;"></div>

<script type="text/javascript">
$(document).ready(function() {
	initialise_tabs('#tabs_login');
	initialise_tabs('#tabs_login1');
});
</script>

<?php
page_footer();