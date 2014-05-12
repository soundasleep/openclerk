<?php
global $user;
?>

<h1>Finance Categories</h1>

<p>
	<?php echo htmlspecialchars(get_site_config('site_name')); ?> Finance can
	keep track of <a href="<?php echo htmlspecialchars(url_for('your_transactions')); ?>">your cryptocurrency transactions</a>
	and group them into separate finance categories
	for tax or accounting purposes.

	Once you have added a finance category, you can <a href="<?php echo htmlspecialchars(url_for('your_transactions')); ?>">add and remove transactions</a> associated with this category.
</p>

<!--<p class="tip tip_float your_account_limits">-->
<p>
As a <?php echo $user['is_premium'] ? "premium" : "free"; ?> user,
you may have up to <?php echo plural(get_premium_value($user, 'finance_categories'), t("finance category"), t("finance categories")); ?> defined.
<?php if (!$user['is_premium']) { ?>
To increase these limits, please purchase a <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium account</a>.
<?php } ?>
</p>
