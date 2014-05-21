<?php
global $user;
?>

<?php if (strtotime($user['created_at']) >= strtotime("-1 hour") || require_get("welcome", false)) { ?>
<div class="success">
<ul>
	<li>Welcome to <?php echo htmlspecialchars(get_site_config('site_name')); ?>!</li>
	<li>To get started, first select the currencies that you are interested in below, and then follow the wizards to configure your addresses, accounts and reports.</li>
	<li>If you have any problems in getting set up, please send us <a href="<?php echo htmlspecialchars(url_add('mailto:' . get_site_config('site_email'), array('subject' => 'Problems Setting Up'))); ?>">an e-mail</a>
		or tweet <a class="twitter" href="https://twitter.com/cryptfolio">@cryptfolio</a>.</li>
</ul>
</div>

<?php /* trafficvance conversion tracking pixel for Dennis */ ?>
<script type="text/javascript" src="http://tracking.trafficvance.com/?id=1G18D7G6C0EG334F874F&amp;fetch=0&amp;value=0">
</script>
<noscript><div style="display: inline;"><img height="1" width="1" style="border-style: none;" alt="" src="http://tracking.trafficvance.com/?id=1G18D7G6C0EG334F874F&amp;fetch=1&amp;value=0" /></div></noscript>
<?php } ?>

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
	later, by selecting the "Configure Accounts" link above.)
</p>

<!--<p class="tip tip_float your_account_limits">-->
<p>
<?php
echo ht("As a :user, you may have up to :accounts defined.",
	array(
		':user' => $user['is_premium'] ? ht("premium user") : ht("free user"),
		':accounts' => plural(get_premium_value($user, 'summaries'), "currency and exchange selection"),
	));
echo "\n";
if (!$user['is_premium']) {
	echo t("To increase this limit, please purchase a :premium_account.", array(':premium_account' => link_to(url_for('premium'), ht("premium account"))));
}
?>
</p>
