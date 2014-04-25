<h2>What is <?php echo get_site_config('site_name'); ?> Finance?</h2>

<p>
	<b>Finance</b> is a new feature in Openclerk 0.22, where you can keep track of your cryptocurrency
	transactions, using any of the cryptocurrency or fiat currencies supported by <?php echo get_site_config('site_name'); ?>.
</p>

<p>
	Note that this feature is still in draft stage, and there may be bugs or usability issues in using these
	new inferfaces. If you find any problems with the Finance interface, please <a href="<?php echo htmlspecialchars(url_for('contact')); ?>">let us know</a>.
</p>

<p>
	<ul class="help_list">
<?php
$knowledge = get_knowledge_base();
$a = $knowledge['Finance'];

	foreach ($a as $key => $kb) {
		$title = $kb;
		if (is_array($kb)) {
			// inline help
			$title = $kb['title'];
		}
		echo "<li><a href=\"" . htmlspecialchars(url_for('kb', array('q' => $key))) . "\">" . htmlspecialchars($title) . "</a>" . ((is_array($kb) && isset($kb['new']) && $kb['new']) ? " <span class=\"new\">new</span>" : "") . "</li>\n";
	}
?>
	</ul>
</p>

