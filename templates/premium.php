
<h1>Support <?php echo htmlspecialchars(get_site_config('site_name')); ?> with Premium Accounts</h1>

<?php if (user_logged_in() && $user = get_user(user_id())) {
	if ($user['is_premium']) { ?>
	<div class="success success_float">
		Thank you for supporting <?php echo htmlspecialchars(get_site_config('site_name')); ?> with
		<a href="<?php echo htmlspecialchars(url_for('user#user_premium')); ?>">your premium account</a>!
		<br>
		Your premium account expires in <?php echo recent_format_html($user['premium_expires'], " ago", "" /* no 'in the future' */); ?>.
	</div>
<?php }
} ?>

<p>
	You can support <?php echo htmlspecialchars(get_site_config('site_name')); ?> by purchasing a
	premium account with <?php
		$result = array();
		foreach (get_site_config('premium_currencies') as $currency) {
			$result[] = get_currency_name($currency);
		}
		echo implode_english($result); ?> currencies. You will also get access to exclusive, premium-only functionality such as
	vastly increased limits on the number of addresses and accounts you may track at once,
	and advanced reporting and notification functionality. Your jobs and reports will also have higher priority over free users.
</p>
