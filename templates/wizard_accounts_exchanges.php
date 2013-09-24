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
<h1>Add Exchanges</h1>

<p>
As a <?php echo $user['is_premium'] ? "premium" : "free"; ?> user,
you may have up to <?php echo number_format(get_premium_value($user, 'accounts')); ?> accounts defined.
<?php if (!$user['is_premium']) { ?>
To increase this limit, please purchase a <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium account</a>.
<?php } ?>
</p>