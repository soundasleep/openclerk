<div class="instructions_add">
<h2>Adding an exchange rate notification</h2>

<p>
	<img src="<?php echo htmlspecialchars(url_for('img/help/notifications.png')); ?>" class="help_inline">

	As of Openclerk 0.16, you can track changes to the exchange rates for any currency pair supported by <?php echo htmlspecialchars(get_site_config('site_name')); ?>
	by configuring <a href="<?php echo htmlspecialchars(url_for('wizard_notifications')); ?>">automated notifications</a>.
</p>

<ol class="steps">
	<li>Log into your <?php echo htmlspecialchars(get_site_config('site_name')); ?> account
		and ensure that you have <a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>">selected currencies</a> you want to be notified for.
		By selecting a pair of currencies, you will be able to track the value of all exchanges that support that currency.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/help/notifications_ticker1.png')); ?>"></li>

	<li>Visit your <a href="<?php echo htmlspecialchars(url_for('wizard_notifications')); ?>">notifications wizard</a>
		and create a new notification of type "exchange rate".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/help/notifications_ticker2.png')); ?>"></li>
</ol>
</div>

<p>
	These automated notifications can be sent out once per day (or once per hour if you are
	a <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium user</a>), and
	are configured through your <a href="<?php echo htmlspecialchars(url_for('wizard_notifications')); ?>">notifications preferences</a>.
</p>

<div style="clear:both;"></div>