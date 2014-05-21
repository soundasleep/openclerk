<h2>Overview</h2>

<p>
	<img src="<?php echo htmlspecialchars(url_for('img/help/notifications_full.png')); ?>" class="help_inline">

	Automated notifications are a new feature added in Openclerk 0.16 that allow you to configure automated
	alerts for particular reports. For example:
</p>

<p>
	<ul>
		<li>Exchange rate on Bitstamp for USD/BTC decreases by 10% within the last day</li>
		<li>Exchange rate on Bitstamp for USD/BTC increases by 10% within the last day</li>
		<li>My converted USD decreases by 10% within the last day</li>
		<li>My converted USD increases by 10% within the last day</li>
		<li>My total LTC hashrate is below 0.1 MH/s within the last hour</li>
	</ul>
</p>

<p>
	These automated notifications can be sent out once per day (or once per hour if you are
	a <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium user</a>), and
	are configured through your <a href="<?php echo htmlspecialchars(url_for('wizard_notifications')); ?>">notifications preferences</a>.
</p>

<h2>Notification types</h2>

<p>
	<ul class="help_list">
<?php
$knowledge = get_knowledge_base();
$a = $knowledge['Notifications'];

	foreach ($a as $key => $kb) {
		$title = $kb;
		if (is_array($kb)) {
			// inline help
			$title = $kb['title'];
		}
		echo "<li><a href=\"" . htmlspecialchars(url_for('kb', array('q' => $key))) . "\">" . htmlspecialchars($title) . "</a>" . ((is_array($kb) && isset($kb['new']) && $kb['new']) ? " <span class=\"new\">" . ht("new") . "</span>" : "") . "</li>\n";
	}
?>
	</ul>
</p>

<div style="clear:both;"></div>
