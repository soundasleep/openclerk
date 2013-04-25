<?php

/**
 * Display user information.
 */

require("inc/global.php");
require_login();

require("layout/templates.php");

$user = get_user(user_id());
if (!$user) {
	throw new Exception("Could not find self user.");
}

$messages = array();
$errors = array();
if (require_post("name", false)) {
	if (!is_valid_name(require_post("name"))) {
		$errors[] = "Invalid name.";
	} else {
		// we can have any name
		$q = db()->prepare("UPDATE users SET name=? WHERE id=? LIMIT 1");
		$q->execute(array(require_post("name"), user_id()));
		$messages[] = "Updated account name.";

		// redirect to GET
		set_temporary_messages($messages);
		redirect(url_for('user'));
	}
}

page_header("User Account", "page_user");

?>

<h1>Your <?php echo htmlspecialchars(get_site_config('site_name')); ?> User Account</h1>

<form action="<?php echo htmlspecialchars(url_for('user')); ?>" method="post">
<table class="form">
<tr>
	<th><label for="user_name">Name:</label></th>
	<td><input id="user_name" name="name" value="<?php echo htmlspecialchars(require_post("name", $user['name'])); ?>" size="32" maxlength="64"> (optional)</td>
</tr>
<tr>
	<th>Member since:</th>
	<td><?php echo recent_format_html($user['created_at']); ?></td>
</tr>
<tr>
	<th>Last login:</th>
	<td><?php echo recent_format_html($user['last_login']); ?></td>
</tr>
<tr>
	<td colspan="2" class="buttons">
		<input type="submit" value="Update Profile">
	</td>
</tr>
</table>
</form>

<h2>Account Status</h2>

<table class="form">
<tr>
	<th>Account status:</th>
	<td>
		<?php if ($user['is_admin']) {
			echo "Administrator";
		} else if ($user['is_premium']) {
			echo "Premium account";
		} else {
			echo "Free account";
		} ?>
	</td>
</tr>
<?php if ($user['is_admin']) { ?>
<tr>
	<th>Expires in:</th>
	<td><i>never</i></td>
</tr>
<?php } else if ($user['is_premium']) { ?>
<tr>
	<th>Expires in:</th>
	<td><?php echo recent_format_html($user['premium_expires'], "" /* no 'in the future' */); ?></td>
</tr>
<?php } ?>
</table>

<?php
page_footer();
