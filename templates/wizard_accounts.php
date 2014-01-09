<?php
global $user;
?>
<div class="wizard-steps">
	<h2>Preferences Wizard</h2>
	<ul>
		<li class="past"><a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>">Currencies</a></li>
		<li class="current"><a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">Accounts</a></li>
		<li class=""><a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>">Reports</a></li>
		<li class=""><a href="<?php echo htmlspecialchars(url_for('wizard_notifications')); ?>">Notifications</a></li>
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
As a <?php echo $user['is_premium'] ? "premium" : "free"; ?> user,
you may have up to <?php echo number_format(get_premium_value($user, 'addresses')); ?> addresses and <?php echo number_format(get_premium_value($user, 'addresses')); ?> accounts defined.
<?php if (!$user['is_premium']) { ?>
To increase these limits, please purchase a <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium account</a>.
<?php } ?>
</p>