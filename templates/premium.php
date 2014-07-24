
<h1><?php echo t("Support :site_name with Premium Accounts"); ?></h1>

<?php if (user_logged_in() && $user = get_user(user_id())) {
	if ($user['is_premium']) { ?>
	<div class="success success_float">
		<?php echo t("Thank you for supporting :site_name with :premium!", array(':premium' => link_to(url_for('user#user_premium'), ht("your premium account")))); ?>
		<br>
		<?php echo t("Your premium account expires in :time.", array(":time" => recent_format_html($user['premium_expires'], " ago", "" /* no 'in the future' */))); ?>
	</div>
<?php }
} ?>

<p>
	<?php
	$result = array();
	foreach (get_site_config('premium_currencies') as $currency) {
		$result[] = get_currency_name($currency);
	}
	echo t("You can support :site_name by purchasing a
	premium account with :currencies currencies. You will also get access to exclusive, premium-only functionality such as
	vastly increased limits on the number of addresses and accounts you may track at once,
	and advanced reporting and notification functionality. Your jobs and reports will also have higher priority over free users.",
		array(":currencies" => implode_english($result)));
	?>
</p>
