<?php require(__DIR__ . "/../graphs/managed.php"); ?>
<h2>Basics</h2>

<p>
<i>Managed graphs</i> are a new feature in Openclerk 0.9 where your reports page, rather than generated just once
at signup, can be updated automatically when you add <a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>">new currencies and accounts</a>,
or when new features and graphs are
added to <?php echo htmlspecialchars(get_site_config('site_name')); ?>.
</p>

<p>
Three options of managed graphs are available on your <a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>">report preferences</a> page:
</p>

<dl>
	<dt>Automatic</dt>
	<dd>Graphs are selected from the <?php
	$graphs = get_auto_managed_graph_categories();
	$categories = get_managed_graph_categories();
	$result = array();
	foreach ($graphs as $key) {
		$result[] = "\"" . $categories[$key] . "\"";
	}
	echo implode_english($result);
	?> portfolio preferences categories. The managed graph page will be reset and updated automatically (see below).</dd>

	<dt>Managed based on my portfolio preferences</dt>
	<dd>By selecting a number of portfolio preferences, you may define which categories of portfolio graphs you are interested in.
	The managed graph page will be updated automatically, but will not change the layout or properties of graphs modified individually (see below).</dd>

	<dt>Self-managed</dt>
	<dd>Recommended for experts: Your graphs will never be automatically updated.</dd>
</dl>

<h2>Automatic updates</h2>

<p>
Your defined graphs will be automatically updated when:
</p>

<ul>
	<li>You switch your <a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>">report preferences</a> to "automatic" or "managed based on my portfolio preferences".</li>
	<li>You update your preferred cryptocurrency.</li>
	<li>You update your preferred fiat currency.</li>
	<li>A new release of Openclerk is deployed to <?php echo htmlspecialchars(get_site_config('site_name')); ?>.</li>
	<li>Graphs will <i>not</i> be automatically updated if your <a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>">report preferences</a> are set to "self-managed", regardless
		of the conditions above.</li>
</ul>

<p>
Automatic updates will remove old graphs that are deemed no longer necessary, but
will not modify the order, layout, parameters, or technical indicators applied to any remaining graphs.
Automatic updates will never change graphs on a page that
is not automatically managed.
</p>

<h2>Automatic resets</h2>

<p>
Your defined graphs will be automatically removed and reset when:
</p>

<ul>
	<li>You switch your <a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>">report preferences</a> to "automatic".</li>
	<li>A new release of Openclerk is deployed to <?php echo htmlspecialchars(get_site_config('site_name')); ?>, and your <a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>">report preferences</a> are set to "automatic".</li>
</ul>

<p>
Automatic resets will reset an entire page and remove any graphs on that page. The order, layout, parameters, and any technical
indicators applied to a graph will also be reset. Automatic resets will never change graphs on a page that
is not automatically managed.
</p>
