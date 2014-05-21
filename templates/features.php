<div class="features-block" id="features_block_top">
<div class="splash"></div>
<h1>Features</h1>

<p>
	<?php echo htmlspecialchars(get_site_config('site_name')); ?> is a web application packed with features
	designed to help you track your cryptocurrencies, investments and mining hardware; understand and analyse markets;
	and make informed decisions about your portfolio.
</p>

<div class="screenshots_group">
<ul class="screenshots">
<?php if (user_logged_in()) { ?>
	<li class="profile"><a href="<?php echo htmlspecialchars(url_for('profile')); ?>">View your Reports</a></li>
<?php } else { ?>
	<li class="signup"><a href="<?php echo htmlspecialchars(url_for('signup')); ?>">Signup for Free</a></li>
<?php } ?>
</ul>
</div>
</div>

<hr>

<div class="features-block feature-left" id="features_currencies">
<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_user_currencies')); ?>" title="Screenshots on cryptocurrency selection"><div class="splash"></div></a>
<h3>Cryptocurrencies</h3>

<p>
	A wide range of cryptocurrencies and fiat currencies can be selected; this ensures that you are only informed about currencies you are actually interested in.
</p>

<p>
	Currently <?php echo htmlspecialchars(get_site_config('site_name')); ?> supports the <?php
	$result = array();
	foreach (get_all_cryptocurrencies() as $c) {
		$result[] = "<span class=\"currency_name_" . htmlspecialchars($c) . "\">" . htmlspecialchars(get_currency_name($c)) . " (" . get_currency_abbr($c) . ")</span>" .
			(in_array($c, get_new_supported_currencies()) ? " <span class=\"new\">new</span>" : "");
	}
	echo implode_english($result);
	?> cryptocurrencies.
</p>

<p>
	<?php echo htmlspecialchars(get_site_config('site_name')); ?> also supports the <?php
	$result = array();
	foreach (get_all_fiat_currencies() as $c) {
		$result[] = "<span class=\"currency_name_" . htmlspecialchars($c) . "\">" . htmlspecialchars(get_currency_abbr($c)) . "</span>" .
			(in_array($c, get_new_supported_currencies()) ? " <span class=\"new\">new</span>" : "");
	}
	echo implode_english($result);
	?> fiat currencies.
</p>

<p>
	<a href="<?php echo htmlspecialchars(url_for('kb?q=cryptocurrencies')); ?>">What are cryptocurrencies?</a>
</p>
</div>

<hr>

<div class="features-block feature-right" id="features_accounts">
<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_accounts')); ?>" title="Screenshots on configuring accounts"><div class="splash"></div></a>
<h3>Track accounts &amp; addresses</h3>

<p>
	<?php echo htmlspecialchars(get_site_config('site_name')); ?> supports a wide range of cryptocurrency applications
	and is regularly updated with new services. For example <?php echo htmlspecialchars(get_site_config('site_name')); ?>
	currently supports:
</p>

<p>
	<dl>
	<?php
	foreach (account_data_grouped() as $category => $datas) {
		if ($category == 'Hidden' || $category == 'Individual Securities') {
			continue;
		}

		echo "<dt>" . htmlspecialchars($category) . "</dt>\n";
		$result = array();
		foreach ($datas as $exchange => $data) {
			if (isset($data['disabled']) && $data['disabled']) {
				// don't display disabled accounts
				continue;
			}
			if ($category == 'Addresses') {
				$result[] = $data['title'] . (in_array($data['currency'], get_new_supported_currencies()) ? " <span class=\"new\">new</span>" : "");
			} else {
				$new = in_array($exchange, get_new_security_exchanges()) || in_array($exchange, get_new_supported_wallets()) || in_array($exchange, get_new_exchanges());
				$result[] = get_exchange_name($exchange)
					. ($new ? " <span class=\"new\">new</span>" : "");
			}
		}
		$result = array_unique($result);	// remove duplicate titles such as Mining Foreman
		natcasesort ($result);
		echo "<dd>" . implode(", ", $result) . "</dd>\n";
	}
	?>
	</dl>
</p>

<p>
	To read account data, you instruct your account provider to enable read-only access via an <i>API key</i>, and you provide that key to <?php echo htmlspecialchars(get_site_config('site_name')); ?>. Helpful wizards guide you through the steps to add new accounts and addresses.
</p>
</div>

<hr>

<div class="features-block feature-left" id="features_reports">
<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_profile_summary')); ?>" title="Screenshots on reports and graphs"><div class="splash"></div></a>
<h3>Reports</h3>

<p>
	Once you have defined some addresses or accounts, you can construct your own personalised summary pages, displaying any information you deem relevant.
	These report pages are made up of graphs, and include helpful reports such as:
</p>

<p>
	<ul>
		<li>The combined fiat value of all of your currencies</li>
		<li>Composition graphs of each currency</li>
		<li>Historical securities trading values</li>
		<li>Historical exchange rates</li>
		<li>... and <a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_profile_summary')); ?>">dozens more</a></li>
	</ul>
</p>

<p>
	Each graph is configurable by currencies, securities, date range, and layout properties.
</p>
</div>

<hr>

<div class="features-block feature-right" id="features_configurable">
<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_profile_addgraph')); ?>" title="Screenshots on how to configure reports and graphs"><div class="splash"></div></a>
<h3>Configurable</h3>

<p>
	<?php echo htmlspecialchars(get_site_config('site_name')); ?> automatically manages your reports based on your currency, address and reporting preferences.
	Alternatively, a simple interface lets you add new graph types, reconfigure them, and reorder them.
</p>

<p>
	Premium users can access an automatically-generated page listing all of their securities and their current market values. Premium users can also
	create <i>pages</i> to categorise different report types.
</p>
</div>

<hr>

<div class="features-block feature-left" id="features_notifications">
<a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'notifications'))); ?>" title="How do automatic notifications work?"><div class="splash"></div></a>
<h3>Notifications <span class="new">new</span></h3>

<p>
	Along with generated report pages, <?php echo htmlspecialchars(get_site_config('site_name')); ?> can also automatically
	<a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'notifications'))); ?>">notify you</a> via e-mail when exchange rates, miner hashrates, or your report values change. For example:
</p>

<p>
	<ul>
		<li>When Bitstamp USD/BTC increases by 10% in a day</li>
		<li>When your LTC hashrate goes below 100 KH/s</li>
		<li>When your BTC balance increases</li>
		<li>When your total portfolio value decreases by 10% in a week</li>
	</ul>
</p>

<p>
	These automated notifications are sent out up to once per day (or once per hour for premium users).
</p>
</div>

<hr>

<div class="features-block feature-right" id="features_historical">
<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_profile_historical')); ?>" title="Screenshots on historical data and graphs"><div class="splash"></div></a>
<h3>Historical</h3>

<p>
	Historical data, both for your accounts, exchanges and securities, can be included in your generated reports.
	Public historical data is also available through the <a href="<?php echo htmlspecialchars(url_for('historical')); ?>">historical data archive</a>.
	Data is downloaded regularly, and in the future can be used in more complex analytical reports.
</p>

<p>
	Premium users can add technical indicators to all graphs, such as Simple Moving Average (SMA), Bollinger Bands (BOLL) and Relative Strength Index (RSI).
</p>
</div>

<div class="screenshots_group">
<ul class="screenshots">
<?php if (user_logged_in()) { ?>
	<li class="profile"><a href="<?php echo htmlspecialchars(url_for('profile')); ?>">View your Reports</a></li>
<?php } else { ?>
	<li class="signup"><a href="<?php echo htmlspecialchars(url_for('signup')); ?>">Signup for Free</a></li>
<?php } ?>
	<li><a href="<?php echo htmlspecialchars(url_for('help')); ?>">Frequently Asked Questions</a></li>
</ul>
</div>

