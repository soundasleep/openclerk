<?php
global $user;
?>

<h1><?php echo ht("Finance Accounts"); ?></h1>

<p>
	<?php echo t(":site_name Finance can
	keep track of :your_transactions
	and assign them to separate accounts,
	for tax or accounting purposes.", array(':your_transactions' => link_to(url_for('your_transactions'), ht("your cryptocurrency transactions")))); ?>

	<?php echo t("Once you have added a finance account, you can :add_remove associated with this account.",
		array(':add_remove' => link_to(url_for('your_transactions'), ht("add and remove transactions")))); ?>
</p>

<!--<p class="tip tip_float your_account_limits">-->
<p>
<?php
echo ht("As a :user, you may have up to :accounts defined.",
	array(
		':user' => $user['is_premium'] ? ht("premium user") : ht("free user"),
		':accounts' => plural(get_premium_value($user, 'finance_accounts'), "finance account"),
	));
echo "\n";
if (!$user['is_premium']) {
	echo t("To increase this limit, please purchase a :premium_account.", array(':premium_account' => link_to(url_for('premium'), ht("premium account"))));
}
?>
</p>
