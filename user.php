<?php

/**
 * Display user information.
 */

require(__DIR__ . "/inc/global.php");
require_login();

require(__DIR__ . "/layout/templates.php");

$user = get_user(user_id());
require_user($user);
$old_email = $user['email'];

$messages = array();
$errors = array();
$name = require_post("name", false);
$email = trim(require_post("email", false));

if ($name !== false && $email !== false) {
	if ($name !== "" && !is_valid_name($name)) {
		$errors[] = "Invalid name.";
	} else if ($email !== "" && !is_valid_email($email)) {
		$errors[] = "Invalid e-mail.";
	} else {
		$subscribe = $email ? (require_post("subscribe", false) ? 1 : 0) : 0;		// if we have no e-mail, we can't subscribe

		// we can have any name
		$q = db()->prepare("UPDATE users SET updated_at=NOW(),name=?,email=?,subscribe_announcements=? WHERE id=? LIMIT 1");
		$q->execute(array($name, $email, $subscribe, user_id()));
		$messages[] = "Updated account details.";

		// subscribe/unsubscribe
		if ($subscribe != $user['subscribe_announcements'] || ($subscribe && $user['email'] != $email)) {
			$q = db()->prepare("DELETE FROM pending_subscriptions WHERE user_id=?");
			$q->execute(array(user_id()));

			if ($email) {
				$q = db()->prepare("INSERT INTO pending_subscriptions SET user_id=?,created_at=NOW(),is_subscribe=?");
				$q->execute(array($user['id'], $subscribe));

				if ($subscribe) {
					$messages[] = "You will be added manually to the <a href=\"http://groups.google.com/group/" . htmlspecialchars(get_site_config('google_groups_announce')) . "\" target=\"_blank\">Announcements Mailing List</a> soon.";
				} else {
					$messages[] = "You will be removed manually from the <a href=\"http://groups.google.com/group/" . htmlspecialchars(get_site_config('google_groups_announce')) . "\" target=\"_blank\">Announcements Mailing List</a> soon.";
				}
			}
		}

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

$q = db()->prepare("SELECT outstanding_premiums.*, ab.created_at AS last_check, ab.balance AS last_balance,
	addresses.address, addresses.currency FROM outstanding_premiums
	LEFT JOIN addresses ON outstanding_premiums.address_id=addresses.id
	LEFT JOIN (SELECT * FROM address_balances WHERE is_recent=1) AS ab ON ab.address_id=addresses.id
	WHERE outstanding_premiums.user_id=? AND is_paid=0 AND is_unpaid=0
	ORDER BY outstanding_premiums.created_at DESC");
$q->execute(array(user_id()));
$outstanding = $q->fetchAll();

$q = db()->prepare("SELECT outstanding_premiums.*,
	premium_addresses.address, premium_addresses.currency FROM outstanding_premiums
	LEFT JOIN premium_addresses ON outstanding_premiums.premium_address_id=premium_addresses.id
	WHERE outstanding_premiums.user_id=? AND (is_paid=1 OR is_unpaid=1)
	ORDER BY outstanding_premiums.created_at DESC");
$q->execute(array(user_id()));
$previous = $q->fetchAll();

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

// get all of our accounts limits
$accounts = user_limits_summary(user_id());

page_header("User Account", "page_user", array('jquery' => true, 'common_js' => true));

?>

<?php if (!$user['email']) { ?>
<div class="warning">
<ul>
	<li>Warning: Without a valid e-mail address specified in your contact details, you will not receive important announcements
		and notifications about your accounts and user profile.</li>
</ul>
</div>
<?php } ?>

<?php if (strtotime($user['created_at']) >= strtotime("-1 hour") || require_get("welcome", false)) { ?>
<div class="success">
<ul>
	<li>Welcome to <?php echo htmlspecialchars(get_site_config('site_name')); ?>!</li>
	<li>To get started, you should update your <a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>">currency, accounts and reporting preferences</a>.</li>
</ul>
</div>
<?php } ?>

<h1>Your <?php echo htmlspecialchars(get_site_config('site_name')); ?> User Account</h1>

<div class="tabs" id="tabs_user">
	<ul class="tab_list">
		<?php /* each <li> must not have any whitespace between them otherwise whitespace will appear when rendered */ ?>
		<li id="tab_user_contact">Contact Details</li><li id="tab_user_premium">Premium</li><li id="tab_user_outstanding">Outstanding Payments</li><li id="tab_user_mailinglist">Mailing List</li>
	</ul>

	<ul class="tab_groups">
	<li id="tab_user_contact_tab">

<form action="<?php echo htmlspecialchars(url_for('user')); ?>" method="post">
<table class="user-profile">
<tr>
	<th><label for="user_name">Name:</label></th>
	<td><input id="user_name" name="name" size="32" value="<?php echo htmlspecialchars(require_post("name", $user['name'] ? $user['name'] : false)); ?>" size="32" maxlength="64"></td>
</tr>
<tr>
	<th><label for="user_email">E-mail:</label></th>
	<td><input id="user_email" name="email" size="48" value="<?php echo htmlspecialchars(require_post("email", $user['email'] ? $user['email'] : false)); ?>" size="32" maxlength="64"></td>
</tr>
<tr>
	<th></th>
	<td><label><input type="checkbox" name="subscribe" value="1"<?php echo $user['subscribe_announcements'] ? " checked" : ""; ?>> Subscribe to <a href="#user_mailinglist">site announcements</a></label></td>
</tr>
<tr>
	<th>Account status:</th>
	<td>
		<a href="#user_premium"><?php if ($user['is_admin']) {
			echo "Administrator";
		} else if ($user['is_system']) {
			echo "System account";
		} else if ($user['is_premium']) {
			echo "Premium account";
		} else {
			echo "Free account";
		} ?></a>
	</td>
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

<div style="margin-top: 1em;">
<!-- TODO remove: added to help users adapt to new wizard_currencies -->
Looking for your <a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>">currency preferences</a>?
</div>

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
<tr>
	<th><a href="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses')); ?>">Tracked addresses</a>:</th>
	<td><?php echo number_format($accounts['total_addresses']); ?> (out of <?php echo number_format(get_premium_value($user, 'addresses')); ?>)</td>
</tr>
<tr>
	<th><a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">Tracked accounts</a>:</th>
	<td><?php echo number_format($accounts['total_accounts']); ?> (out of <?php echo number_format(get_premium_value($user, 'accounts')); ?>)</td>
</tr>
<tr>
	<th><a href="<?php echo htmlspecialchars(url_for('profile')); ?>">Summary pages</a>:</th>
	<td><?php echo number_format($accounts['total_graph_pages']); ?> (out of <?php echo number_format(get_premium_value($user, 'graph_pages')); ?>)</td>
</tr>
<tr>
	<th><a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>">Currencies</a>:</th>
	<td><?php echo number_format($accounts['total_summaries']); ?> (out of <?php echo number_format(get_premium_value($user, 'summaries')); ?>)</td>
</tr>
</table>

<p>
<?php if (!$user['is_premium']) { ?>
Support <?php echo htmlspecialchars(get_site_config('site_name')); ?> and get access to
more features with a <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium account</a>!
<?php } else { ?>
Extend your <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium account</a> here:
<?php } ?>
</p>

<?php if ($outstanding) { ?>
<p><b>NOTE:</b> You already have <a href="<?php echo htmlspecialchars(url_for("user#user_outstanding")); ?>">outstanding premium payments</a> that need to be paid.</p>
<?php } ?>

<?php require(__DIR__ . "/_premium_prices.php"); ?>
</div>

	</li>
	<li id="tab_user_outstanding_tab">

<?php if ($outstanding) { ?>
<h2>Outstanding Payments</h2>

<table class="standard fancy">
<thead>
	<tr>
		<th>Currency</th>
		<th>Premium</th>
		<th>Address</th>
		<th class="number">Due</th>
		<th class="number">Balance</th>
		<th>Since</th>
		<th>Last checked</th>
	</tr>
</thead>
<tbody>
<?php $count = 0; foreach ($outstanding as $o) { ?>
	<tr class="<?php echo ++$count % 2 == 0 ? "odd" : "even"; ?>">
		<td><span class="currency_name_<?php echo htmlspecialchars($o['currency']); ?>"><?php echo htmlspecialchars(get_currency_name($o['currency'])); ?></span></td>
		<td><?php echo $o['months'] ? plural($o['months'], "month") : ""; echo $o['years'] ? plural($o['years'], "year") : ""; ?></td>
		<td><?php echo crypto_address($o['currency'], $o['address']); ?></td>
		<td class="number"><?php echo currency_format($o['currency'], $o['balance']); ?></td>
		<td class="number"><?php echo currency_format($o['currency'], $o['last_balance'] ? $o['last_balance'] : 0); ?></td>
		<td><?php echo recent_format_html($o['created_at']); ?></td>
		<td><?php echo recent_format_html($o['last_check']); ?></td>
	</tr>
<?php } ?>
</tbody>
</table>

<p class="warning-inline">
<b>NOTE:</b> Outstanding payments will be automatically cancelled after <?php echo plural(get_site_config('outstanding_abandon_days'), 'day'); ?>.
</p>

<?php } else { ?>
	<p><i>No outstanding payments.</i></p>
<?php } ?>

<?php if ($previous) { ?>
<h2>Previous Payments</h2>

<table class="standard fancy">
<thead>
	<tr>
		<th>Currency</th>
		<th>Premium</th>
		<th>Address</th>
		<th class="number">Due</th>
		<th class="number">Balance</th>
		<th>Paid</th>
	</tr>
</thead>
<tbody>
<?php $count = 0; foreach ($previous as $o) { ?>
	<tr class="<?php echo ++$count % 2 == 0 ? "odd" : "even"; ?>">
		<td><span class="currency_name_<?php echo htmlspecialchars($o['currency']); ?>"><?php echo htmlspecialchars(get_currency_name($o['currency'])); ?></span></td>
		<td><?php echo $o['months'] ? plural($o['months'], "month") : ""; echo $o['years'] ? plural($o['years'], "year") : ""; ?></td>
		<td><?php echo crypto_address($o['currency'], $o['address']); ?></td>
		<td class="number"><?php echo currency_format($o['currency'], $o['balance']); ?></td>
		<td class="number"><?php echo currency_format($o['currency'], $o['paid_balance']); ?></td>
		<td><?php echo recent_format_html($o['created_at']); ?></td>
	</tr>
<?php } ?>
</tbody>
</table>

<?php } else { ?>
	<p><i>No previous payments.</i></p>
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
		<a href="https://groups.google.com/group/<?php echo htmlspecialchars(get_site_config('google_groups_announce')); ?>" target="_blank"><img width="132" alt="Google Groups"
		src="https://groups.google.com/groups/img/3nb/groups_bar.gif" height="26"></a>
		<a href="https://groups.google.com/group/<?php echo htmlspecialchars(get_site_config('google_groups_announce')); ?>" target="_blank" class="visit">Visit this group</a>
	</div>
	<form action="https://groups.google.com/group/<?php echo htmlspecialchars(get_site_config('google_groups_announce')); ?>/boxsubscribe" target="_blank">
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
