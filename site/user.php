<?php

/**
 * Display user information.
 */

require(__DIR__ . "/../inc/global.php");
require_login();

require(__DIR__ . "/../layout/templates.php");

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
	} else if (!$email && $user['password_hash']) {
		$errors[] = "You cannot remove your e-mail address until you have disabled <a href=\"" . htmlspecialchars(url_for('user#user_password')) . "\">password login</a> on this account.";
	}

	// check that there are no existing users with this e-mail address
	if ($email && $user['password_hash']) {
		$q = db()->prepare("SELECT * FROM users WHERE email=? AND ISNULL(password_hash) = 0 AND id <> ?");
		$q->execute(array($email, $user['id']));

		if ($q->fetch()) {
			$errors[] = t("That e-mail address is already in use by another account using password login.");
		}
	}

	if (!$errors) {

		$subscribe = $email ? (require_post("subscribe", false) ? 1 : 0) : 0;		// if we have no e-mail, we can't subscribe
		$disable_graph_refresh = require_post("disable_graph_refresh", false) ? 1 : 0;

		// we can have any name
		$q = db()->prepare("UPDATE users SET updated_at=NOW(),name=?,email=?,subscribe_announcements=?,disable_graph_refresh=? WHERE id=? LIMIT 1");
		$q->execute(array($name, $email, $subscribe, $disable_graph_refresh, user_id()));
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
			send_email($email, $email, $old_email ? "change_email" : "new_email", array(
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
			$qr_code = url_for('qr/' . $p['address']);
			$messages[] = "<div class=\"inline_qr\"><img class=\"qr\" src=\"" . htmlspecialchars($qr_code) . "\">" .
				"Your premium purchase is now pending on payment - please deposit " .
				currency_format($p['currency'], $p['balance']) . " into the address " .
				crypto_address($p['currency'], $p['address']) . ". Once the " .
				get_currency_name($p['currency']) . " network has confirmed the transaction (after " . get_site_config($p['currency'] . '_confirmations') . " blocks), your account will be credited with premium status. Thank you!" .
				"</div>";
		}
	}
}

// get all of our accounts limits
$accounts = user_limits_summary(user_id());

$q = db()->prepare("SELECT * FROM openid_identities WHERE user_id=? ORDER BY url ASC");
$q->execute(array(user_id()));
$identities = $q->fetchAll();

page_header(t("User Account"), "page_user", array('js' => 'user'));

?>

<?php if (!$user['email']) { ?>
<div class="warning">
<ul>
	<li><?php echo ht("Warning: Without a valid e-mail address specified in your contact details, you will not receive important announcements
		and notifications about your accounts and user profile."); ?></li>
</ul>
</div>
<?php } ?>

<?php if (strtotime($user['created_at']) >= strtotime("-1 hour") || require_get("welcome", false)) { ?>
<div class="success">
<ul>
	<li><?php echo ht("Welcome to :site_name!"); ?></li>
	<li><?php echo ht("To get started, you should update your :preferences.", array(
			':preferences' => "<a href=\"" . htmlspecialchars(url_for('wizard_currencies')) . "\">" . ht("currency, accounts and reporting preferences") . "</a>")); ?></li>
</ul>
</div>
<?php } ?>

<h1><?php echo ht("Your :site_name User Account"); ?></h1>

<div class="tabs" id="tabs_user">
	<ul class="tab_list">
		<?php /* each <li> must not have any whitespace between them otherwise whitespace will appear when rendered */ ?>
		<li id="tab_user_contact"><?php echo ht("Contact Details"); ?></li><li id="tab_user_password"><?php echo ht("Password"); ?></li><li id="tab_user_openid"><?php echo ht("Identities"); ?></li><li id="tab_user_premium"><?php echo ht("Premium"); ?></li><li id="tab_user_outstanding"><?php echo ht("Outstanding Payments"); ?></li><li id="tab_user_mailinglist"><?php echo ht("Mailing List"); ?></li>
	</ul>

	<ul class="tab_groups">
	<li id="tab_user_contact_tab">

<form action="<?php echo htmlspecialchars(url_for('user')); ?>" method="post">
<table class="user-profile">
<tr>
	<th><label for="user_name"><?php echo ht("Name:"); ?></label></th>
	<td><input id="user_name" name="name" size="32" value="<?php echo htmlspecialchars(require_post("name", $user['name'] ? $user['name'] : false)); ?>" size="32" maxlength="64"></td>
</tr>
<tr>
	<th><label for="user_email"><?php echo ht("E-mail:"); ?></label></th>
	<td><input id="user_email" name="email" size="48" value="<?php echo htmlspecialchars(require_post("email", $user['email'] ? $user['email'] : false)); ?>" size="32" maxlength="64"></td>
</tr>
<tr>
	<th></th>
	<td><label><input type="checkbox" name="disable_graph_refresh" value="1"<?php echo $user['disable_graph_refresh'] ? " checked" : ""; ?>> <?php echo ht("Disable automatic graph refresh"); ?></label></td>
</tr>
<tr>
	<th></th>
	<td><label><input type="checkbox" name="subscribe" value="1"<?php echo $user['subscribe_announcements'] ? " checked" : ""; ?>> Subscribe to <a href="#user_mailinglist">site announcements</a></label></td>
</tr>
<tr>
	<th><?php echo ht("Account status:"); ?></th>
	<td>
		<a href="#user_premium"><?php if ($user['is_admin']) {
			echo ht("Administrator");
		} else if ($user['is_system']) {
			echo ht("System account");
		} else if ($user['is_premium']) {
			echo ht("Premium account");
		} else {
			echo ht("Free account");
		} ?></a>
	</td>
</tr>
<tr>
	<th><?php echo ht("Member since:"); ?></th>
	<td><?php echo recent_format_html($user['created_at']); ?></td>
</tr>
<tr>
	<th><?php echo ht("Last login:"); ?></th>
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
	<li id="tab_user_password_tab" style="display:none;">

<div class="tip tip_float">
	<a class="password-openid-switch" href="<?php echo htmlspecialchars(url_for('signup', array('use_password' => false))); ?>">OpenID login</a>
	is often much more secure than using an e-mail and password. If you do use a password, please
	make sure you do not use the same password on other cryptocurrency sites.
</div>

<?php if (!$user['password_hash']) { ?>

<h2><?php echo ht("Enable e-mail/password login"); ?></h2>

<p>
	You have not enabled e-mail/password login on your account.
	Adding a password will not increase security,
	and you should continue to login with your <a href="<?php echo htmlspecialchars(url_for('user#user_openid')); ?>">OpenID identities</a>.
</p>

<?php if (!$user['email']) { ?>

<p>
	You cannot enable e-mail/password login on your account as
	you first need to <a href="<?php echo htmlspecialchars(url_for('user#user_contact')); ?>">add an e-mail address</a>.
</p>

<?php } else { ?>

<?php
// check there are no other accounts using a password hash on this e-mail address
$q = db()->prepare("SELECT * FROM users WHERE email=? AND ISNULL(password_hash) = 0 AND id <> ?");
$q->execute(array($user['email'], user_id()));
if ($q->fetch()) {
?>

<p>
	You cannot enable e-mail/password login on your account as
	this e-mail address is already in use by another account for password login.
</p>

<?php } else { ?>

<p class="show-password-form">
	<a><?php echo ht("Enable e-mail/password login on your account"); ?></a>
</p>

<form action="<?php echo htmlspecialchars(url_for('set_password')); ?>" method="post" class="add-password-form" style="display:none;">
<table class="user-profile">
<tr>
	<th><?php echo ht("E-mail:"); ?></th>
	<td><?php echo htmlspecialchars($user['email']); ?></td>
</tr>
<tr>
	<th><label for="password"><?php echo ht("Password:"); ?></label></th>
	<td>
		<input type="password" id="password" name="password" size="32" value="" maxlength="255"> <span class="required">*</span>
	</td>
</tr>
<tr>
	<th><label for="password2"><?php echo ht("Repeat:"); ?></label></th>
	<td>
		<input type="password" id="password2" name="password2" size="32" value="" maxlength="255"> <span class="required">*</span>
	</td>
</tr>
<tr>
	<td colspan="2" class="buttons">
		<input type="submit" value="<?php echo ht("Add password"); ?>">
	</td>
</tr>
</table>
</form>

<?php } ?>
<?php } ?>

<?php } else { ?>

<h2><?php echo ht("Change password"); ?></h2>

<p>
	Your password was last changed <?php echo recent_format_html($user['password_last_changed']); ?>.
</p>

<form action="<?php echo htmlspecialchars(url_for('set_password')); ?>" method="post">
<table class="user-profile">
<tr>
	<th><?php echo ht("E-mail:"); ?></th>
	<td><?php echo htmlspecialchars($user['email']); ?></td>
</tr>
<tr>
	<th><label for="password"><?php echo ht("Password:"); ?></label></th>
	<td>
		<input type="password" id="password" name="password" size="32" value="" maxlength="255"> <span class="required">*</span>
	</td>
</tr>
<tr>
	<th><label for="password2"><?php echo ht("Repeat:"); ?></label></th>
	<td>
		<input type="password" id="password2" name="password2" size="32" value="" maxlength="255"> <span class="required">*</span>
	</td>
</tr>
<tr>
	<td colspan="2" class="buttons">
		<input type="submit" value="<?php echo ht("Add password"); ?>">
	</td>
</tr>
</table>
</form>

<hr>

<h2><?php echo ht("Remove password login"); ?></h2>

<p>
	Once you have added at least one <a href="<?php echo htmlspecialchars(url_for('user#user_openid')); ?>">OpenID identity</a>
	to your account, you can disable e-mail/password login on your account to increase security.
	You will then have to use your OpenID identities to login in the future.
</p>

<?php if ($identities) { ?>
<form action="<?php echo htmlspecialchars(url_for('delete_password')); ?>" method="post">
<table class="user-profile">
<tr>
	<td>
		<label><input type="checkbox" name="confirm" value="1"> <?php echo ht("Disable e-mail/password login"); ?></label>
	</td>
</tr>
<tr>
	<td class="buttons">
		<input type="submit" value="Remove password">
	</td>
</tr>
</table>
</form>

<?php } else { ?>

<p>
	You cannot disable e-mail/password login on your account until you add at least one
	<a href="<?php echo htmlspecialchars(url_for('user#user_openid')); ?>">OpenID identity</a>.
</p>

<?php } ?>

<?php } ?>

	</li>
	<li id="tab_user_openid_tab" style="display:none;">

<h2><?php echo ht("Your OpenID Identites"); ?></h2>

<table class="standard fancy openid_list">
<thead>
	<tr>
		<th><?php echo ht("Provider"); ?></th>
		<th><?php echo ht("Identity"); ?></th>
		<th><?php echo ht("Added"); ?></th>
		<?php
		/* only allow one identity to be removed */
		if (count($identities) > 1) {
		?>
		<th><?php echo ht("Delete"); ?></th>
		<?php } ?>
	</tr>
</thead>
<tbody>
<?php
$count = 0;
foreach ($identities as $identity) {
	// try and guess the provider
	$provider = "openid_manual";
	foreach (get_openid_provider_formats() as $format => $key) {
		if (preg_match($format, $identity['url'])) {
			$provider = $key;
		}
	}
	$provider_titles = get_default_openid_providers();
	?>
	<tr class="<?php echo ++$count % 2 == 0 ? "odd" : "even"; ?>">
		<td><span class="openid <?php echo htmlspecialchars($provider); ?>"><?php echo isset($provider_titles[$provider]) ? htmlspecialchars($provider_titles[$provider][0]) : 'OpenID'; ?></span></td>
		<td><a href="<?php echo htmlspecialchars(url_for($identity['url'])); ?>"><?php echo htmlspecialchars(url_for($identity['url'])); ?></a></td>
		<td><?php echo recent_format_html($identity['created_at']); ?></td>
		<?php
		/* only allow one identity to be removed */
		if (count($identities) > 1) {
		?>
		<td>
			<form action="<?php echo htmlspecialchars(url_for('openid_delete')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($identity['id']); ?>">
				<input type="submit" value="Delete" class="delete" title="Delete this identity" onclick="return confirm('Are you sure you want to remove this identity?');">
			</form>
		</td>
		<?php } ?>
	</tr>
<?php } ?>
<?php if (!$identities) { ?>
	<tr>
		<td colspan="3"><i><?php echo ht("No OpenID identities defined."); ?></i></td>
	</tr>
<?php } ?>
</tbody>
<tfoot>
	<tr>
		<td colspan="<?php echo count($identities) > 1 ? 4 : 3; ?>" class="buttons">
			<a href="<?php echo htmlspecialchars(url_for('openid_add')); ?>"><?php echo ht("Add another OpenID Identity"); ?></a>
		</td>
	</tr>
</tfoot>
</table>

	</li>
	<li id="tab_user_premium_tab" style="display:none;">

<div class="account_status">
<h2><?php echo ht("Account Status"); ?></h2>

<table class="fancy">
<tr>
	<th>Account status:</th>
	<td>
		<?php if ($user['is_admin']) {
			echo ht("Administrator");
		} else if ($user['is_system']) {
			echo ht("System account");
		} else if ($user['is_premium']) {
			echo ht("Premium account");
		} else {
			echo ht("Free account");
		} ?>
	</td>
</tr>
<?php if ($user['is_premium']) { ?>
<tr>
	<th><?php echo ht("Expires in:"); ?></th>
	<td><?php echo recent_format_html($user['premium_expires'], " ago", "" /* no 'in the future' */); ?></td>
</tr>
<?php } ?>
<tr>
	<th><a href="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses')); ?>"><?php echo ht("Tracked addresses"); ?></a>:</th>
	<td><?php echo number_format($accounts['total_addresses']); ?> (out of <?php echo number_format(get_premium_value($user, 'addresses')); ?>)</td>
</tr>
<tr>
	<th><a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>"><?php echo ht("Tracked accounts"); ?></a>:</th>
	<td><?php echo number_format($accounts['total_accounts']); ?> (out of <?php echo number_format(get_premium_value($user, 'accounts')); ?>)</td>
</tr>
<tr>
	<th><a href="<?php echo htmlspecialchars(url_for('wizard_notifications')); ?>"><?php echo ht("Notifications"); ?></a>:</th>
	<td><?php echo number_format($accounts['total_notifications']); ?> (out of <?php echo number_format(get_premium_value($user, 'notifications')); ?>)</td>
</tr>
<tr>
	<th><a href="<?php echo htmlspecialchars(url_for('profile')); ?>"><?php echo ht("Summary pages"); ?></a>:</th>
	<td><?php echo number_format($accounts['total_graph_pages']); ?> (out of <?php echo number_format(get_premium_value($user, 'graph_pages')); ?>)</td>
</tr>
<tr>
	<th><a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>"><?php echo ht("Currencies"); ?></a>:</th>
	<td><?php echo number_format($accounts['total_summaries']); ?> (out of <?php echo number_format(get_premium_value($user, 'summaries')); ?>)</td>
</tr>
<tr>
	<th><a href="<?php echo htmlspecialchars(url_for('finance_accounts')); ?>"><?php echo ht("Finance Accounts"); ?></a>:</th>
	<td><?php echo number_format($accounts['total_finance_accounts']); ?> (out of <?php echo number_format(get_premium_value($user, 'finance_accounts')); ?>)</td>
</tr>
<tr>
	<th><a href="<?php echo htmlspecialchars(url_for('finance_categories')); ?>"><?php echo ht("Finance Categories"); ?></a>:</th>
	<td><?php echo number_format($accounts['total_finance_categories']); ?> (out of <?php echo number_format(get_premium_value($user, 'finance_categories')); ?>)</td>
</tr>
</table>

<p>
<?php if (!$user['is_premium']) { ?>
Support <?php echo htmlspecialchars(get_site_config('site_name')); ?> and get access to
more features with a <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium account</a>!
<?php } else { ?>
Thank you for supporting <?php echo htmlspecialchars(get_site_config('site_name')); ?>!
Extend your <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium account</a> here:
<?php } ?>
</p>

<?php if ($outstanding) { ?>
<p><b>NOTE:</b> You already have <a href="<?php echo htmlspecialchars(url_for("user#user_outstanding")); ?>">outstanding premium payments</a> that need to be paid.</p>
<?php } ?>

<?php require(__DIR__ . "/_premium_prices.php"); ?>
</div>

	</li>
	<li id="tab_user_outstanding_tab" style="display:none;">

<?php if ($outstanding) { ?>
<h2><?php echo ht("Outstanding Payments"); ?></h2>

<table class="standard fancy">
<thead>
	<tr>
		<th><?php echo ht("Currency"); ?></th>
		<th><?php echo ht("Premium"); ?></th>
		<th><?php echo ht("Address"); ?></th>
		<th class="number"><?php echo ht("Due"); ?></th>
		<th class="number"><?php echo ht("Balance"); ?></th>
		<th><?php echo ht("Since"); ?></th>
		<th><?php echo ht("Last checked"); ?></th>
	</tr>
</thead>
<tbody>
<?php $count = 0; foreach ($outstanding as $o) { ?>
	<tr class="<?php echo ++$count % 2 == 0 ? "odd" : "even"; ?>">
		<td><span class="currency_name_<?php echo htmlspecialchars($o['currency']); ?>"><?php echo htmlspecialchars(get_currency_name($o['currency'])); ?></span></td>
		<td><?php echo $o['months'] ? plural("month", $o['months']) : ""; echo $o['years'] ? plural("year", $o['years']) : ""; ?></td>
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
<b><?php echo ht("NOTE:"); ?></b> Outstanding payments will be automatically cancelled after <?php echo plural("day", get_site_config('outstanding_abandon_days')); ?>.
</p>

<?php } else { ?>
	<p><i><?php echo ht("No outstanding payments."); ?></i></p>
<?php } ?>

<?php if ($previous) { ?>
<h2><?php echo ht("Previous Payments"); ?></h2>

<table class="standard fancy">
<thead>
	<tr>
		<th><?php echo ht("Currency"); ?></th>
		<th><?php echo ht("Premium"); ?></th>
		<th><?php echo ht("Address"); ?></th>
		<th class="number"><?php echo ht("Due"); ?></th>
		<th class="number"><?php echo ht("Balance"); ?></th>
		<th><?php echo ht("Paid"); ?></th>
	</tr>
</thead>
<tbody>
<?php $count = 0; foreach ($previous as $o) { ?>
	<tr class="<?php echo ++$count % 2 == 0 ? "odd" : "even"; ?>">
		<td><span class="currency_name_<?php echo htmlspecialchars($o['currency']); ?>"><?php echo htmlspecialchars(get_currency_name($o['currency'])); ?></span></td>
		<td><?php echo $o['months'] ? plural("month", $o['months']) : ""; echo $o['years'] ? plural("year", $o['years']) : ""; ?></td>
		<td><?php echo crypto_address($o['currency'], $o['address']); ?></td>
		<td class="number"><?php echo currency_format($o['currency'], $o['balance']); ?></td>
		<td class="number"><?php echo currency_format($o['currency'], $o['paid_balance']); ?></td>
		<td><?php echo recent_format_html($o['created_at']); ?></td>
	</tr>
<?php } ?>
</tbody>
</table>

<?php } else { ?>
	<p><i><?php echo ht("No previous payments."); ?></i></p>
<?php } ?>

	</li>
	<li id="tab_user_mailinglist_tab" style="display:none;">

<h2><?php echo ht("Subscribe to :site_name Announcements"); ?></h2>

<p>
	To keep up to date with news and service updates to <?php echo htmlspecialchars(get_site_config('site_name')); ?>, please subscribe to the
	<?php echo htmlspecialchars(get_site_config('site_name')); ?> Announcements mailing list below.
</p>

<!-- from http://code.google.com/p/gdata-issues/issues/detail?id=27 -->
<div id="groups_subscription">
	<div class="link">
		<a href="https://groups.google.com/group/<?php echo htmlspecialchars(get_site_config('google_groups_announce')); ?>" target="_blank"><img width="132" alt="Google Groups"
		src="https://groups.google.com/groups/img/3nb/groups_bar.gif" height="26"></a>
		<a href="https://groups.google.com/group/<?php echo htmlspecialchars(get_site_config('google_groups_announce')); ?>" target="_blank" class="visit"><?php echo ht("Visit this group"); ?></a>
	</div>
	<form action="https://groups.google.com/group/<?php echo htmlspecialchars(get_site_config('google_groups_announce')); ?>/boxsubscribe" target="_blank">
	<label class="email"><?php echo ht("E-mail:"); ?>
	<input name="email" type="text" size="32" value="<?php echo htmlspecialchars($user['email']); ?>" /></label>
	<input value="<?php echo ht("Subscribe"); ?>" name="sub" type="submit" />
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
