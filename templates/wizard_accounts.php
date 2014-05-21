<?php
global $user;
?>
<div class="wizard-steps">
	<h2>Preferences Wizard</h2>
	<ul>
		<li class="past"><a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>">Currencies</a></li>
		<li class="current"><a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">Accounts</a></li>
		<li class=""><a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>">Reports</a></li>
		<li class=""><a href="<?php echo htmlspecialchars(url_for('profile')); ?>">Your Reports</a></li>
	</ul>
</div>

<div class="wizard-content">
<h1>Add Accounts and Addresses</h1>

<p>
	<?php echo htmlspecialchars(get_site_config('site_name')); ?> keeps track of your
	cryptocurrencies, investments and equities by regularly downloading account details
	from accounts that you define. You can add five different types of accounts as
	illustrated below. (You can always change these accounts
	later, by selecting the "Configure Accounts" link above.)
</p>

<!--<p class="tip tip_float your_account_limits">-->
<p>
<?php
echo ht("As a :user, you may have up to :addresses and :accounts defined.",
	array(
		':user' => $user['is_premium'] ? ht("premium user") : ht("free user"),
		':addresses' => plural("address", "addresses", get_premium_value($user, 'addresses')),
		':accounts' => plural("account", get_premium_value($user, 'accounts')),
	));
echo "\n";
if (!$user['is_premium']) {
	echo t("To increase these limits, please purchase a :premium_account.", array(':premium_account' => link_to(url_for('premium'), ht("premium account"))));
}
?>
</p>
