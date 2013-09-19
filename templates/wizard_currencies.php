<div class="wizard-steps">
	<h2>Preferences Wizard</h2>
	<ul>
		<li class="current"><a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>">Currencies</a></li>
		<li class=""><a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">Accounts</a></li>
		<li class=""><a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>">Reports</a></li>
		<li class=""><a href="<?php echo htmlspecialchars(url_for('profile')); ?>">Your Reports</a></li>
	</ul>
</div>

<div class="wizard-content">
<h1>Currency Preferences</h1>

<p>
	Welcome to <?php echo htmlspecialchars(get_site_config('site_name')); ?>!
	To begin tracking your investments and addresses, please first select the
	currencies that you are interested in. (You can always change these options
	later, by selecting the "Preferences" link in the navigation menu.)
</p>