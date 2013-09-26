<?php

/**
 * This page does the hard work of displaying what accounts a user currently has enabled.
 * We delegate adding/deleting accounts to each of the separate account pages.
 */

require("inc/global.php");
require_login();

require("layout/templates.php");
page_header("Your Accounts", "page_accounts");

$user = get_user(user_id());
require_user($user);

$messages = array();
if (get_temporary_messages()) {
	$messages += get_temporary_messages();
}

// get all of our accounts limits
$accounts = user_limits_summary(user_id());

?>

<?php if ($messages) { ?>
<div class="message">
<ul>
	<?php foreach ($messages as $m) { echo "<li>" . $m . "</li>"; } /* do NOT accept user input for messages! */ ?>
</ul>
</div>
<?php } ?>

<h1>Your Accounts</h1>

<div class="your_accounts">
<?php if ($accounts['total_addresses'] == 0 && $accounts['total_accounts'] == 0) { ?>
<div class="success">
<ul>
	<li>You do not yet have any accounts or addresses defined. Click on any of the account types below to get started.</li>
</ul>
</div>
<?php } ?>

<ul class="account_list">
<?php

function callback_sort_title($a, $b) {
	return strcmp($a['title'], $b['title']);
}
foreach (account_data_grouped() as $label => $account_data) {
	if ($label == "Hidden")
		continue;

	// add titles, and sort by title
	foreach ($account_data as $key => $value) {
		// if we don't specify a plural, we assume it's just adding 's'
		if (!isset($account_data[$key]['labels'])) {
			$account_data[$key]['labels'] = $account_data[$key]['label'] . "s";
		}
	}
	uasort($account_data, 'callback_sort_title');

	echo "<li>" . htmlspecialchars($label) . "\n<ul>\n";
	foreach ($account_data as $key => $value) {
		echo "<li><strong>" . htmlspecialchars($value['title']) . ":</strong> ";
		echo "<a href=\"" . url_for($value['url']) . "\">";
		echo (isset($accounts[$key]) && $accounts[$key]) ? (number_format($accounts[$key]) . " " . ($accounts[$key] == 1 ? $value['label'] : $value['labels'])) : "<i>none</i>";
		echo "</a>";
		echo "</li>\n";
	}
	echo "</ul></li>\n";
}
?>
</ul>
</div>

<div class="your_account_limits">
<h2>Your Account Limits</h2>

<ul>
	<li>Tracked addresses: <?php echo number_format($accounts['total_addresses']); ?> (out of <?php echo number_format(get_premium_value($user, 'addresses')); ?>)</li>
	<li>Tracked accounts: <?php echo number_format($accounts['total_accounts']); ?> (out of <?php echo number_format(get_premium_value($user, 'accounts')); ?>)</li>
	<li><a href="<?php echo htmlspecialchars(url_for('profile')); ?>">Summary pages</a>: <?php echo number_format($accounts['total_graph_pages']); ?> (out of <?php echo number_format(get_premium_value($user, 'graph_pages')); ?>)</li>
	<li><a href="<?php echo htmlspecialchars(url_for('user')); ?>">Currencies</a>: <?php echo number_format($accounts['total_summaries']); ?> (out of <?php echo number_format(get_premium_value($user, 'summaries')); ?>)</li>
	<?php if ($user['is_premium']) { ?>
	<li>Thank you for supporting <?php echo htmlspecialchars(get_site_config('site_name')); ?> with your premium account!</li>
	<?php } else { ?>
	<li>Increase these limits with a <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium account</a>!</li>
	<?php } ?>
</ul>
</div>

<?php
page_footer();
