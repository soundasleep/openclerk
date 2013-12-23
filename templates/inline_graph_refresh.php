<h2>Overview</h2>

<p>
	<img src="<?php echo htmlspecialchars(url_for('img/help/graph_refresh.png')); ?>" class="help_inline">

	Yes - as of Openclerk 0.15, graphs throughout the site will update themselves automatically with the most recent data.
	By default, graphs will only update themselves every <?php echo plural(get_site_config('graph_refresh_public'), 'minute'); ?>;
	graphs displayed to <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium users</a> will update
	themselves every <?php echo plural(get_site_config('graph_refresh_premium'), 'minute'); ?>.
</p>

<p>
	You may disable live graph refreshes by logging into <a href="<?php echo htmlspecialchars(url_for('user')); ?>">your user account</a>
	and disabling "automatic graph refresh".
</p>

<div style="clear:both;"></div>