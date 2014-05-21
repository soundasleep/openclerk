<h2>Overview</h2>

<p>
	We want to add support for as many fiat currencies as we can to
	<a href="http://openclerk.org" target="_blank">Openclerk</a> (the underlying open source project),
	with priority on fiat currencies that are in highest demand.
</p>

<p>
	Currently <?php echo htmlspecialchars(get_site_config('site_name')); ?> supports the <?php
	$result = array();
	foreach (get_all_fiat_currencies() as $c) {
		$result[] = "<span class=\"currency_name_" . htmlspecialchars($c) . "\">" . htmlspecialchars(get_currency_name($c)) . "</span>" .
			(in_array($c, get_new_supported_currencies()) ? " <span class=\"new\">" . ht("new") . "</span>" : "");
	}
	echo implode_english($result);
	?> fiat currencies.
</p>

<h2>Requesting a new fiat currency</h2>

<p>
	If you would like Openclerk to support a new fiat currency, please let us know through one of the following methods:
</p>

<?php require_template('inline_contact'); ?>

<p>
	If you would like to increase the priority of adding your preferred fiat currency to
	Openclerk, you might want to consider <a href="<?php echo htmlspecialchars(url_for('help')); ?>">sponsoring the task</a>
	or supporting <?php echo htmlspecialchars(get_site_config('site_name')); ?> by
	<a href="<?php echo htmlspecialchars(url_for('premium')); ?>">purchasing a premium account</a>.
</p>
