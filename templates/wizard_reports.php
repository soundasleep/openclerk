<?php
global $user;
?>
<div class="wizard-steps">
	<h2>Preferences Wizard</h2>
	<ul>
		<li class="past"><a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>">Currencies</a></li>
		<li class="past"><a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">Accounts</a></li>
		<li class="current"><a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>">Reports</a></li>
		<li class=""><a href="<?php echo htmlspecialchars(url_for('profile')); ?>">Your Reports</a></li>
	</ul>
</div>

<div class="wizard-content">
<h1>Report Preferences</h1>

<p>
	Once information from your accounts have been downloaded and compiled into reports,
	you may view these reports in many ways by defining graphs and pages.
	<?php echo htmlspecialchars(get_site_config('site_name')); ?> can automatically
	manage these reports for you, or you may opt to define these manually.
	(You can always change these accounts
	later, by selecting the "Preferences" link above.)
</p>
