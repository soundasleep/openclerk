<?php
global $user;
global $account_type;
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
<h1><?php echo t("Add :titles", array(':titles' => $account_type['titles'])); ?></h1>

<p>
<?php
echo t("Summary calculations will also include any currency values defined below as :offsets.", array(':offsets' => "<i>" . t("offsets") . "</i>"));
echo "\n";
echo ht("As a :user, you may have up to :offsets defined.",
	array(
		':user' => $user['is_premium'] ? ht("premium user") : ht("free user"),
		':offsets' => plural("offset", get_premium_value($user, 'offsets')),
	));
echo "\n";
if (!$user['is_premium']) {
	echo t("To increase this limit, please purchase a :premium_account.", array(':premium_account' => link_to(url_for('premium'), ht("premium account"))));
}
?>
</p>
