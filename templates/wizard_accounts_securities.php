<?php
global $user;
global $account_type;
global $accounts;
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
<h1>Add <?php echo $account_type['titles']; ?></h1>

<ul class="account-type floating">

	<li><a href="<?php echo htmlspecialchars(url_for('wizard_accounts_individual_securities')); ?>">Individual Securities
		<?php if ($accounts['wizard_individual']) { ?><span class="count">(<?php echo number_format($accounts['wizard_individual']); ?>)<?php } ?></a>
		<ul>
			<li>e.g. ASICMINER, S.DICE-PT</li>
		</ul>
	</li>

</ul>

<p>
<?php
echo ht("As a :user, you may have up to :accounts defined.",
	array(
		':user' => $user['is_premium'] ? ht("premium user") : ht("free user"),
		':accounts' => plural("account", get_premium_value($user, 'accounts')),
	));
echo "\n";
if (!$user['is_premium']) {
	echo t("To increase this limit, please purchase a :premium_account.", array(':premium_account' => link_to(url_for('premium'), ht("premium account"))));
}
?>
</p>
