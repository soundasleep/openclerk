<h2>Overview</h2>

<p>
	We want to add support for as many cryptocurrencies as we can to
	<a href="http://openclerk.org" target="_blank">Openclerk</a> (the underlying open source project),
	with priority on cryptocurrencies that are in highest demand. The requirements for supporting
	any cryptocurrency are:
</p>

<p>
	<ul>
		<li>There needs to be a stable, public explorer API to query for exact account balances with a given number of transactions.</li>
		<li>This explorer API should also be able to list the current block number, and list recent transactions in a standard format.</li>
		<li>For example, a port of <a href="https://github.com/jtobey/bitcoin-abe">Abe</a> is usually sufficient.</li>
	</ul>
</p>

<div class="tip">
Cryptocurrencies that do <em>not</em> have a suitable explorer API yet at the time of writing:
<ul>
	<li><span class="currency_name_smc">Smartcoin</span></li>
</ul>
</div>

<p>
	Currently <?php echo htmlspecialchars(get_site_config('site_name')); ?> supports the <?php
	$result = array();
	foreach (get_all_cryptocurrencies() as $c) {
		$result[] = "<span class=\"currency_name_" . htmlspecialchars($c) . "\">" . htmlspecialchars(get_currency_name($c)) . "</span>" .
			(in_array($c, get_new_supported_currencies()) ? " <span class=\"new\">new</span>" : "");
	}
	echo implode_english($result);
	?> cryptocurrencies.
</p>

<p>
	In the future, <?php echo htmlspecialchars(get_site_config('site_name')); ?> will host cryptocurrency
	explorer instances locally, removing these requirements and making it possible to add almost any
	cryptocurrency; please donate or purchase a <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium account</a> to
	support this upcoming feature.
</p>

<h2>Requesting a new cryptocurrency</h2>

<p>
	If you would like Openclerk to support a new cryptocurrency, please let us know through one of the following methods:
</p>

<?php require_template('inline_contact'); ?>

<p>
	If you would like to increase the priority of adding your preferred cryptocurrency to
	Openclerk, you might want to consider <a href="<?php echo htmlspecialchars(url_for('help')); ?>">sponsoring the task</a>
	or supporting <?php echo htmlspecialchars(get_site_config('site_name')); ?> by
	<a href="<?php echo htmlspecialchars(url_for('premium')); ?>">purchasing a premium account</a>.
</p>
