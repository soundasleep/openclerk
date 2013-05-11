<?php

/**
 * Display user information.
 */

require("inc/global.php");
require_login();

require("layout/templates.php");

$user = get_user(user_id());
require_user($user);
$old_email = $user['email'];

$messages = array();
$errors = array();
if (require_post("name", false) !== false && require_post("email", false) !== false) {
	if (require_post("name") !== "" && !is_valid_name(require_post("name"))) {
		$errors[] = "Invalid name.";
	} else if (require_post("email") !== "" && !is_valid_email(require_post("email"))) {
		$errors[] = "Invalid e-mail.";
	} else {
		$email = require_post("email");

		// we can have any name
		$q = db()->prepare("UPDATE users SET updated_at=NOW(),name=?,email=? WHERE id=? LIMIT 1");
		$q->execute(array(require_post("name"), $email, user_id()));
		$messages[] = "Updated account details.";

		// try sending email
		if ($email && $email != $old_email) {
			send_email($email, $email, "change_email", array(
				"old_email" => $old_email ? $old_email : "(no previous e-mail address)",
				"email" => $email,
				"url" => absolute_url(url_for("unsubscribe", array('email' => $email, 'hash' => md5(get_site_config('unsubscribe_salt') . $email)))),
			));
		}

		// redirect to GET
		set_temporary_messages($messages);
		redirect(url_for('user'));
	}
}

$q = db()->prepare("SELECT outstanding_premiums.*, ab.created_at AS last_check,
	addresses.address, addresses.currency FROM outstanding_premiums
	LEFT JOIN addresses ON outstanding_premiums.address_id=addresses.id
	LEFT JOIN (SELECT * FROM address_balances WHERE is_recent=1) AS ab ON ab.address_id=addresses.id
	WHERE outstanding_premiums.user_id=? AND is_paid=0 AND is_unpaid=0");
$q->execute(array(user_id()));
$outstanding = $q->fetchAll();

if (require_get("new_purchase", false)) {
	// find the new purchase
	foreach ($outstanding as $p) {
		if ($p['id'] == require_get("new_purchase")) {
			$messages[] = "Your premium purchase is now pending on payment - please deposit " .
				currency_format($p['currency'], $p['balance']) . " into the address " .
				crypto_address($p['currency'], $p['address']) . ". Once the " .
				get_currency_name($p['currency']) . " network has confirmed the transaction (after " . get_site_config($p['currency'] . '_confirmations') . " blocks), your account will be credited with premium status. Thank you!";
		}
	}
}

page_header("User Account", "page_user", array('jquery' => true, 'common_js' => true));

?>

<h1>Your <?php echo htmlspecialchars(get_site_config('site_name')); ?> User Account</h1>

<?php if (strtotime("-1 hour") < strtotime($user['created_at'])) { ?>
<div class="success">
<ul>
	<li>Welcome to <?php echo htmlspecialchars(get_site_config('site_name')); ?>!</li>
	<li>To get started, you should first confirm the currencies that you are interested in below.</li>
	<li>After that, head to your <a href="<?php echo htmlspecialchars(url_for('accounts')); ?>">accounts page</a>
		to link in your mining pools, exchanges and cryptocurrency addresses.</li>
</ul>
</div>
<?php } ?>

<div class="tabs" id="tabs_user">
	<ul class="tab_list">
		<?php /* each <li> must not have any whitespace between them otherwise whitespace will appear when rendered */ ?>
		<li id="tab_user_currencies">Currencies</li><li id="tab_user_premium">Premium</li><li id="tab_user_contact">Contact Details</li><li id="tab_user_outstanding">Outstanding Payments</li>
	</ul>

	<ul class="tab_groups">
	<li id="tab_user_currencies_tab">

<h2>Currency Settings</h2>

<p class="tip tip_float">
Once you have selected currencies, you should head over to your
<a href="<?php echo htmlspecialchars(url_for('accounts')); ?>">accounts page</a> and link up
your existing addresses and exchanges.
</p>

<form action="<?php echo htmlspecialchars(url_for('user_currencies')); ?>" method="post">
<?php require("_user_currencies.php"); ?>
<input type="submit" value="Update Currency Settings">
</form>

	</li>
	<li id="tab_user_premium_tab">

<div class="account_status">
<h2>Account Status</h2>

<table class="fancy">
<tr>
	<th>Account status:</th>
	<td>
		<?php if ($user['is_admin']) {
			echo "Administrator";
		} else if ($user['is_system']) {
			echo "System account";
		} else if ($user['is_premium']) {
			echo "Premium account";
		} else {
			echo "Free account";
		} ?>
	</td>
</tr>
<?php if ($user['is_premium']) { ?>
<tr>
	<th>Expires in:</th>
	<td><?php echo recent_format_html($user['premium_expires'], " ago", "" /* no 'in the future' */); ?></td>
</tr>
<?php } ?>
</table>

<p>
<?php if (!$user['is_premium']) { ?>
Support <?php echo htmlspecialchars(get_site_config('site_name')); ?> and get access to
more features with a <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium account</a>!
<?php } else { ?>
Extend your <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium account</a> here:
<?php } ?>
</p>

<?php require("_premium_prices.php"); ?>
</div>

	</li>
	<li id="tab_user_contact_tab">

<form action="<?php echo htmlspecialchars(url_for('user')); ?>" method="post">
<table class="standard form">
<tr>
	<th><label for="user_name">Name:</label></th>
	<td><input id="user_name" name="name" value="<?php echo htmlspecialchars(require_post("name", $user['name'] ? $user['name'] : false)); ?>" size="32" maxlength="64"> (optional)</td>
</tr>
<tr>
	<th><label for="user_email">E-mail:</label></th>
	<td><input id="user_email" name="email" value="<?php echo htmlspecialchars(require_post("email", $user['email'] ? $user['email'] : false)); ?>" size="32" maxlength="64"> (optional)</td>
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

	</li>
	<li id="tab_user_outstanding_tab">

<?php if ($outstanding) { ?>
<h2>Outstanding Payments</h2>

<table class="standard">
<thead>
	<tr>
		<th>Currency</th>
		<th>Address</th>
		<th>Amount</th>
		<th>Since</th>
		<th>Last checked</th>
	</tr>
</thead>
<tbody>
<?php foreach ($outstanding as $o) { ?>
	<tr>
		<td><?php echo htmlspecialchars(get_currency_name($o['currency'])); ?></td>
		<td><?php echo crypto_address($o['currency'], $o['address']); ?></td>
		<td><?php echo currency_format($o['currency'], $o['balance']); ?></td>
		<td><?php echo recent_format_html($o['created_at']); ?></td>
		<td><?php echo recent_format_html($o['last_check']); ?></td>
	</tr>
<?php } ?>
</tbody>
</table>

<?php } else { ?>
	<i>No outstanding payments.</i>
<?php } ?>

	</li>
</ul>

<script type="text/javascript">
$(document).ready(function() {
	initialise_tabs('#tabs_user');
});
</script>

<?php
page_footer();
