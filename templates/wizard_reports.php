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
	you may view <a href="<?php echo htmlspecialchars(url_for('profile')); ?>">these reports</a> by defining graphs and pages.
	<?php echo htmlspecialchars(get_site_config('site_name')); ?> can automatically
	manage these reports for you, or you may opt to define these manually.
	(You can always change these accounts
	later, by selecting the "Configure Accounts" link above.)
</p>

<!--<p class="tip tip_float your_account_limits">-->
<p>
As a <?php echo $user['is_premium'] ? "premium" : "free"; ?> user,
you may have up to <?php echo plural(get_premium_value($user, 'graphs_per_page'), "graph"); ?> per page.
<?php if (!$user['is_premium']) { ?>
To increase this limit, please purchase a <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium account</a>.
<?php } ?>
</p>

<p><a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'managed_graphs'))); ?>"><?php echo htmlspecialchars(get_knowledge_base_title('managed_graphs')); ?></a></p>
