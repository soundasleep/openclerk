<div class="features-block" id="features_block_top">
<div class="splash"></div>
<h1><?php echo ht("Features"); ?></h1>

<p>
	<?php echo ht(":site_name is a web application packed with features
	designed to help you track your cryptocurrencies, investments and mining hardware; understand and analyse markets;
	and make informed decisions about your portfolio."); ?>
</p>

<div class="screenshots_group">
<ul class="screenshots">
<?php if (user_logged_in()) { ?>
	<li class="profile"><a href="<?php echo htmlspecialchars(url_for('profile')); ?>"><?php echo ht("View your Reports"); ?></a></li>
<?php } else { ?>
	<li class="signup"><a href="<?php echo htmlspecialchars(url_for('signup')); ?>"><?php echo ht("Signup for Free"); ?></a></li>
<?php } ?>
</ul>
</div>
</div>

<hr>

<div class="features-block feature-left" id="features_currencies">
<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_user_currencies')); ?>" title="<?php echo ht("Screenshots on cryptocurrency selection"); ?>"><div class="splash"></div></a>
<h3><?php echo ht("Cryptocurrencies"); ?></h3>

<p>
	<?php echo ht("A wide range of cryptocurrencies and fiat currencies can be selected; this ensures that you are only informed about currencies you are actually interested in."); ?>
</p>

<p>
	<?php
	$result = array();
	foreach (get_all_cryptocurrencies() as $c) {
		$result[] = "<span class=\"currency_name_" . htmlspecialchars($c) . "\">" . htmlspecialchars(get_currency_name($c)) . " (" . get_currency_abbr($c) . ")</span>" .
			(in_array($c, get_new_supported_currencies()) ? " <span class=\"new\">" . ht("new") . "</span>" : "");
	}
	echo t("Currently :site_name supports the :currencies cryptocurrencies.",
		array(
			':currencies' => implode_english($result),
		));
	?>
</p>

<p>
	<?php
	$result = array();
	foreach (get_all_fiat_currencies() as $c) {
		$result[] = "<span class=\"currency_name_" . htmlspecialchars($c) . "\">" . htmlspecialchars(get_currency_abbr($c)) . "</span>" .
			(in_array($c, get_new_supported_currencies()) ? " <span class=\"new\">" . ht("new") . "</span>" : "");
	}
	echo t(":site_name also supports the :currencies fiat currencies.",
		array(
			':currencies' => implode_english($result),
		));
	?>
</p>

<p>
	<a href="<?php echo htmlspecialchars(url_for('kb?q=cryptocurrencies')); ?>"><?php echo ht("What are cryptocurrencies?"); ?></a>
</p>
</div>

<hr>

<div class="features-block feature-right" id="features_accounts">
<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_accounts')); ?>" title="<?php echo ht("Screenshots on configuring accounts"); ?>"><div class="splash"></div></a>
<h3><?php echo ht("Track accounts & addresses"); ?></h3>

<p>
	<?php echo ht(":site_name supports a wide range of cryptocurrency applications
	and is regularly updated with new services. For example :site_name
	currently supports:"); ?>
</p>

<p>
	<dl>
	<?php
	foreach (account_data_grouped() as $category => $datas) {
		if ($category == 'Hidden' || $category == 'Individual Securities' || $category == 'Finance') {
			continue;
		}

		echo "<dt>" . ht($category) . "</dt>\n";
		$result = array();
		foreach ($datas as $exchange => $data) {
			if (isset($data['disabled']) && $data['disabled']) {
				// don't display disabled accounts
				continue;
			}
			if ($category == 'Addresses') {
				$result[] = $data['title'] . (in_array($data['currency'], get_new_supported_currencies()) ? " <span class=\"new\">" . ht("new") . "</span>" : "");
			} else {
				$new = in_array($exchange, get_new_security_exchanges()) || in_array($exchange, get_new_supported_wallets()) || in_array($exchange, get_new_exchanges());
				$result[] = get_exchange_name($exchange)
					. ($new ? " <span class=\"new\">" . ht("new") . "</span>" : "");
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
	<?php echo t("To read account data, you instruct your account provider to enable read-only access via an :api_key,
		and you provide that key to :site_name. Helpful wizards guide you through the steps to add new accounts and addresses.",
		array(':api_key' => "<i>" . ht("API key") . "</i>")); ?>
</p>
</div>

<hr>

<div class="features-block feature-left" id="features_reports">
<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_profile_summary')); ?>" title="<?php echo ht("Screenshots on reports and graphs"); ?>"><div class="splash"></div></a>
<h3><?php echo ht("Reports"); ?></h3>

<p>
	<?php echo ht("Once you have defined some addresses or accounts, you can construct your own personalised summary pages, displaying any information you deem relevant.
	These report pages are made up of graphs, and include helpful reports such as:"); ?>
</p>

<p>
	<ul>
		<li><?php echo t("The combined fiat value of all of your currencies"); ?></li>
		<li><?php echo t("Composition graphs of each currency"); ?></li>
		<li><?php echo t("Historical securities trading values"); ?></li>
		<li><?php echo t("Historical exchange rates"); ?></li>
		<li><?php echo t("... and :dozens_more", array(':dozens_more' => link_to(url_for('screenshots#screenshots_profile_summary'), ht("dozens more")))); ?></li>
	</ul>
</p>

<p>
	Each graph is configurable by currencies, securities, date range, and layout properties.
</p>
</div>

<hr>

<div class="features-block feature-right" id="features_configurable">
<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_profile_addgraph')); ?>" title="<?php echo ht("Screenshots on how to configure reports and graphs"); ?>"><div class="splash"></div></a>
<h3><?php echo ht("Configurable"); ?></h3>

<p>
	<?php echo ht(":site_name automatically manages your reports based on your currency, address and reporting preferences.
	Alternatively, a simple interface lets you add new graph types, reconfigure them, and reorder them."); ?>
</p>

<p>
	<?php echo t("Premium users can access an automatically-generated page listing all of their securities and their current market values. Premium users can also
	create :pages to categorise different report types.", array(':pages' => "<i>" . ht("pages") . "</i>")); ?>
</p>
</div>

<hr>

<div class="features-block feature-left" id="features_notifications">
<a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'notifications'))); ?>" title="<?php echo ht("How do automatic notifications work?"); ?>"><div class="splash"></div></a>
<h3><?php echo ht("Notifications"); ?> <span class="new"><?php echo ht("new"); ?></span></h3>

<p>
	<?php echo t("
	Along with generated report pages, :site_name can also automatically
	:notify_you  via e-mail when exchange rates, miner hashrates, or your report values change. For example:
	", array(":notify_you" => link_to(url_for('kb', array('q' => 'notifications')), ht("notify you")))); ?>
</p>

<p>
	<ul>
		<li><?php echo ht("When Bitstamp USD/BTC increases by 10% in a day"); ?></li>
		<li><?php echo ht("When your LTC hashrate goes below 100 KH/s"); ?></li>
		<li><?php echo ht("When your BTC balance increases"); ?></li>
		<li><?php echo ht("When your total portfolio value decreases by 10% in a week"); ?></li>
	</ul>
</p>

<p>
	<?php echo ht("These automated notifications are sent out up to once per day (or once per hour for premium users)."); ?>
</p>
</div>

<hr>

<div class="features-block feature-right" id="features_historical">
<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_profile_historical')); ?>" title="<?php echo ht("Screenshots on historical data and graphs"); ?>"><div class="splash"></div></a>
<h3><?php echo ht("Historical"); ?></h3>

<p>
	<?php echo t("
	Historical data, both for your accounts, exchanges and securities, can be included in your generated reports.
	Public historical data is also available through the :archive.
	Data is downloaded regularly, and in the future can be used in more complex analytical reports.",
	array(
		":archive" => link_to(url_for('historical'), ht("historical data archive")),
	)); ?>
</p>

<p>
	<?php
	echo ht("Premium users can add technical indicators to all graphs, such as :example1, :example2 and :example3.",
		array(
			":example1" => t("Simple Moving Average") . " (SMA)",
			":example2" => t("Bollinger Bands") . " (BOLL)",
			":example3" => t("Relative Strength Index") . " (RSI)",
		)); ?>
</p>
</div>

<div class="screenshots_group">
<ul class="screenshots">
<?php if (user_logged_in()) { ?>
	<li class="profile"><a href="<?php echo htmlspecialchars(url_for('profile')); ?>"><?php echo ht("View your Reports"); ?></a></li>
<?php } else { ?>
	<li class="signup"><a href="<?php echo htmlspecialchars(url_for('signup')); ?>"><?php echo ht("Signup for Free"); ?></a></li>
<?php } ?>
	<li><a href="<?php echo htmlspecialchars(url_for('help')); ?>"><?php echo ht("Frequently Asked Questions"); ?></a></li>
</ul>
</div>

