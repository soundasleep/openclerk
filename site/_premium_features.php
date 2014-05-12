<div class="premium_info">
<table class="standard">
<thead>
	<tr>
		<th>Feature</th>
		<th>Free account</th>
		<th class="premium">Premium account</th>
	</tr>
</thead>
<tbody>
	<?php
	$blockchain = get_blockchain_currencies();
	$currencies = array();
	foreach ($blockchain as $currency_list) {
		foreach ($currency_list as $c) {
			$currencies[$c] = $c;
		}
	}

	$premium_technical_types = 0;
	$free_types = array();
	$premium_types = array();
	foreach (graph_technical_types() as $key => $data) {
		$title = "<abbr title=\"" . htmlspecialchars($data['title']) . "\">" . htmlspecialchars($data['title_short']) . "</abbr>";
		$premium_types[] = $title;
		if ($data['premium']) {
			$premium_technical_types++;
		} else {
			$free_types[] = $title;
		}
	}

	$currencies = array_map('get_currency_abbr', $currencies);
	if (count($currencies) > 5) {
		$other_currencies = array_slice($currencies, 5);
		$currencies = array_slice($currencies, 0, 5);
		$currencies[] = '...<span><a class="collapse-link collapsed">+</a><span class="collapse-target">' . implode(", ", $other_currencies) . '</span></span>';
	}
	$predef = array(
		'addresses' => 'Tracked addresses (' . implode(", ", $currencies) . ')',
		'accounts' => 'Tracked accounts (BTC-e, Mt.Gox, ...)',
		'graph_pages' => $welcome ? "Reports pages" : '<a href="' . htmlspecialchars(url_for('profile')) . '">Reports pages</a>',
		'graphs_per_page' => 'Graphs per report page',
		'summaries' => $welcome ? "Currencies" : '<a href="' . htmlspecialchars(url_for('wizard_currencies')) . '">Currencies</a>',
	);
	foreach ($predef as $key => $title) { ?>
	<tr>
		<th><?php echo $title; ?></th>
		<td class="number"><?php echo number_format(get_premium_config($key . "_free")); ?></td>
		<td class="number premium"><?php echo number_format(get_premium_config($key . "_premium")); ?></td>
	</tr>
	<?php } ?>
	<tr>
		<th>Technical indicator types</th>
		<td class="number"><?php echo number_format(count(graph_technical_types()) - $premium_technical_types); ?> (<?php echo implode(", ", $free_types); ?>)</td>
		<td class="number premium"><?php echo number_format(count(graph_technical_types())); ?> (<?php echo implode(", ", $premium_types); ?>)</td>
	</tr>
	<tr>
		<th><?php echo $welcome ? "Your securities" : "<a href=\"" . htmlspecialchars(url_for('screenshots#screenshots_profile_summary')) . "\">Your securities</a>"; ?> reports</th>
		<?php foreach (array('free', 'premium') as $type) { ?>
		<td class="<?php echo $type . " " . (get_premium_config('your_securities_' . $type) ? "yes" : "no"); ?>"><?php echo get_premium_config('your_securities_' . $type) ? "Y" : "-"; ?></td>
		<?php } ?>
	</tr>
	<tr>
		<th>Priority over free users</th>
		<td class="no">-</td>
		<td class="yes premium">Y</td>
	</tr>
	<tr>
		<th>Must login every</th>
		<td class="number"><?php echo plural(get_site_config('user_expiry_days'), 'day'); ?></td>
		<td class="no premium">Not necessary</td>
	</tr>
	<tr>
		<th>Data updated at least every</th>
		<td class="number"><?php echo plural(get_site_config('refresh_queue_hours'), 'hour'); ?></td>
		<td class="number premium"><?php echo plural(get_site_config('refresh_queue_hours_premium'), 'hour'); ?></td>
	</tr>
	<tr>
		<th><?php echo $welcome ? "Notifications" : '<a href="' . htmlspecialchars(url_for('wizard_notifications')) . '">Notifications</a>'?></th>
		<td class="number"><?php echo number_format(get_premium_config("summaries_free")); ?> (daily)</td>
		<td class="number premium"><?php echo number_format(get_premium_config("summaries_premium")); ?> (hourly)</td>
	</tr>
	<tr>
		<th><?php echo $welcome ? "Finance Accounts" : '<a href="' . htmlspecialchars(url_for('finance_accounts')) . '">Finance Accounts</a>'?></th>
		<td class="number"><?php echo number_format(get_premium_config("finance_accounts_free")); ?></td>
		<td class="number premium"><?php echo number_format(get_premium_config("finance_accounts_premium")); ?></td>
	</tr>
	<tr>
		<th><?php echo $welcome ? "Finance Categories" : '<a href="' . htmlspecialchars(url_for('finance_categories')) . '">Finance Categories</a>'?></th>
		<td class="number"><?php echo number_format(get_premium_config("finance_categories_free")); ?></td>
		<td class="number premium"><?php echo number_format(get_premium_config("finance_categories_premium")); ?></td>
	</tr>
	<tr>
		<th>Export transactions to CSV</th>
		<td class="no">-</td>
		<td class="yes premium">Y</td>
	</tr>
	<tr>
		<th><a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'graph_refresh'))); ?>">Live graph updates</a></th>
		<td class="number"><?php echo plural(get_site_config('graph_refresh_free'), 'minute'); ?></td>
		<td class="number premium"><?php echo plural(get_site_config('graph_refresh_premium'), 'minute'); ?></td>
	</tr>
	<?php if ($welcome) { ?>
	<tr class="payment">
		<th></th>
		<td class="free">
			<form action="<?php echo htmlspecialchars(url_for('user')); ?>" method="get">
			<input type="submit" value="Continue">
			</form>
		</td>
		<td class="premium">
		<?php require(__DIR__ . "/_premium_prices.php"); ?>
		</td>
	</tr>
	<?php } ?>
</tbody>
</table>
</div>
