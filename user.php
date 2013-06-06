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

<?php if (strtotime($user['created_at']) >= strtotime("-1 hour")) { ?>
<div class="success">
<ul>
	<li>Welcome to <?php echo htmlspecialchars(get_site_config('site_name')); ?>!</li>
	<li>To get started, you should first confirm the currencies that you are interested in below.</li>
	<li>After that, head to your <a href="<?php echo htmlspecialchars(url_for('accounts')); ?>">accounts page</a>
		to link in your mining pools, exchanges and cryptocurrency addresses.</li>
	<li>Finally, don&apos;t forget to subscribe to the <a href="http://groups.google.com/group/<?php echo htmlspecialchars(get_site_config('google_groups_announce')); ?>" target="_blank">announcements mailing list</a>.</li>
</ul>
</div>
<?php } ?>

<?php if (get_site_config('new_user_premium_update_hours') && strtotime($user['created_at']) >= strtotime("-" . get_site_config('new_user_premium_update_hours') . " hour")) { ?>
<div class="message">
<ul>
	<li>As a new user, your addresses and accounts will be updated more frequently (every <?php echo plural(get_site_config('refresh_queue_hours_premium'), 'hour'); ?>)
		for the next <?php echo plural(
		(int) (get_site_config('new_user_premium_update_hours') - ((time() - strtotime($user['created_at']))) / (60 * 60))
		, "hour"); ?>.
</ul>
</div>
<?php } ?>

<div class="tabs" id="tabs_user">
	<ul class="tab_list">
		<?php /* each <li> must not have any whitespace between them otherwise whitespace will appear when rendered */ ?>
		<li id="tab_user_currencies">Currencies</li><li id="tab_user_premium">Premium</li><li id="tab_user_contact">Contact Details</li><li id="tab_user_outstanding">Outstanding Payments</li><li id="tab_user_mailinglist">Mailing List</li>
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
		<td><span class="currency_name_<?php echo htmlspecialchars($o['currency']); ?>"><?php echo htmlspecialchars(get_currency_name($o['currency'])); ?></span></td>
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
	<li id="tab_user_mailinglist_tab">

<h2>Subscribe to <?php echo htmlspecialchars(get_site_config('site_name')); ?> Announcements</h2>

<p>
	To keep up to date with news and service updates to <?php echo htmlspecialchars(get_site_config('site_name')); ?>, please subscribe to the
	<?php echo htmlspecialchars(get_site_config('site_name')); ?> Announcements mailing list below.
</p>

<!-- from http://code.google.com/p/gdata-issues/issues/detail?id=27 -->
<div id="groups_subscription">
	<div class="link">
		<a href="http://groups.google.com/group/<?php echo htmlspecialchars(get_site_config('google_groups_announce')); ?>" target="_blank"><img width="132" alt="Google Groups"
		src="http://groups.google.com/groups/img/3nb/groups_bar.gif" height="26"></a>
		<a href="http://groups.google.com/group/<?php echo htmlspecialchars(get_site_config('google_groups_announce')); ?>" target="_blank" class="visit">Visit this group</a>
	</div>
	<form action="http://groups.google.com/group/<?php echo htmlspecialchars(get_site_config('google_groups_announce')); ?>/boxsubscribe" target="_blank">
	<label class="email">Email:
	<input name="email" type="text" size="32" value="<?php echo htmlspecialchars($user['email']); ?>" /></label>
	<input value="Subscribe" name="sub" type="submit" />
	</form>
</div>

	</li>
</ul>

<script type="text/javascript">
$(document).ready(function() {
	initialise_tabs('#tabs_user');
});
</script>

<?php
page_footer();
