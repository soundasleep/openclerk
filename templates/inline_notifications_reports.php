<div class="instructions_add">
<h2>Adding a report notification</h2>

<p>
	<img src="<?php echo htmlspecialchars(url_for('img/help/notifications.png')); ?>" class="help_inline">

	As of Openclerk 0.16, you can track changes to parts of your generated reports
	by configuring <a href="<?php echo htmlspecialchars(url_for('wizard_notifications')); ?>">automated notifications</a>.
</p>

<ol class="steps">
	<li>Log into your <?php echo htmlspecialchars(get_site_config('site_name')); ?> account
		and ensure that you have <a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>">selected a report currency</a>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/help/notifications_reports1.png')); ?>"></li>

	<li>Visit your <a href="<?php echo htmlspecialchars(url_for('wizard_notifications')); ?>">notifications wizard</a>
		and create a new notification of type "my total" or "my converted".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/help/notifications_reports2.png')); ?>"></li>
</ol>
</div>

<p>
	These automated notifications can be sent out once per day (or once per hour if you are
	a <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium user</a>), and
	are configured through your <a href="<?php echo htmlspecialchars(url_for('wizard_notifications')); ?>">notifications preferences</a>.
</p>

<div style="clear:both;"></div>