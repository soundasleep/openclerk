<h2>Overview</h2>

<p>
	<img src="<?php echo htmlspecialchars(url_for('img/help/graph_refresh.png')); ?>" class="help_inline">

	Yes - as of Openclerk 0.15, graphs throughout the site will update themselves automatically with the most recent data.
	By default, graphs will only update themselves every <?php echo plural("minute", get_site_config('graph_refresh_public')); ?>;
	graphs displayed to <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium users</a> will update
	themselves every <?php echo plural("minute", get_site_config('graph_refresh_premium')); ?>.
</p>

<p>
	Note that not all graphs have data that is updated this frequently. Your account data and reports are still only updated
	according to <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">your data update rates</a>
	(once every <?php echo plural("hour", get_premium_config('refresh_queue_hours_free')); ?> for free users,
	or every <?php echo plural("hour", get_premium_config('refresh_queue_hours_premium')); ?> for premium users).
	Other graphs, such as <a href="<?php echo htmlspecialchars(url_for('historical')); ?>">ticker and historical graphs</a>, are updated much more frequently for everyone.
</p>

<p>
	You may disable live graph refreshes by logging into <a href="<?php echo htmlspecialchars(url_for('user')); ?>">your user account</a>
	and disabling "automatic graph refresh".
</p>

<div style="clear:both;"></div>
