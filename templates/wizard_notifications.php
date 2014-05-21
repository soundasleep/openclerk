<?php
global $user;
?>

<div class="wizard-steps">
	<h2><?php echo t("Preferences Wizard"); ?></h2>
	<ul>
		<li class="past"><a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>"><?php echo t("Currencies"); ?></a></li>
		<li class="current"><a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>"><?php echo t("Accounts"); ?></a></li>
		<li class=""><a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>"><?php echo t("Reports"); ?></a></li>
		<li class=""><a href="<?php echo htmlspecialchars(url_for('profile')); ?>"><?php echo t("Your Reports"); ?></a></li>
	</ul>
</div>

<div class="wizard-content">
<h1><?php echo t("Notification Preferences"); ?></h1>

<p>
	<?php echo t(':site_name can also optionally :notify_you when
	your accounts change. (You can always change these options
	later, by selecting the "Configure Accounts" link above.)',
		array(
			':notify_you' => link_to(url_for('kb', array('q' => 'notifications')), t('notify you')),
		));
	?>
</p>

<!--<p class="tip tip_float your_account_limits">-->
<p>
<?php
echo ht("As a :user, you may have up to :notifications defined.",
	array(
		':user' => $user['is_premium'] ? ht("premium user") : ht("free user"),
		':notifications' => plural("configured notification", get_premium_value($user, 'notifications')),
	));
echo "\n";
if (!$user['is_premium']) {
	echo t("To increase this limit, please purchase a :premium_account.", array(':premium_account' => link_to(url_for('premium'), ht("premium account"))));
}
?>
</p>
