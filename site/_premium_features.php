<div class="premium_info">
<table class="standard">
<thead>
	<tr>
		<th><?php echo t("Feature"); ?></th>
		<th><?php echo t("Free account"); ?></th>
		<th class="premium"><?php echo t("Premium account"); ?></th>
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
		'addresses' => t('Tracked addresses (:currencies)', array(':currencies' => implode(", ", $currencies))),
		'accounts' => t('Tracked accounts (:accounts)', array(':accounts' => 'BTC-e, Mt.Gox, ...')),
		'graph_pages' => $welcome ? t("Reports pages") : link_to(url_for('profile'), t("Reports pages")),
		'graphs_per_page' => t("Graphs per report page"),
		'summaries' => $welcome ? t("Currencies") : link_to(url_for('wizard_currencies'), t("Currencies")),
	);
	foreach ($predef as $key => $title) { ?>
	<tr>
		<th><?php echo $title; ?></th>
		<td class="number"><?php echo number_format(get_premium_config($key . "_free")); ?></td>
		<td class="number premium"><?php echo number_format(get_premium_config($key . "_premium")); ?></td>
	</tr>
	<?php } ?>
	<tr>
		<th><?php echo t("Technical indicator types"); ?></th>
		<td class="number"><?php echo number_format(count(graph_technical_types()) - $premium_technical_types); ?> (<?php echo implode(", ", $free_types); ?>)</td>
		<td class="number premium"><?php echo number_format(count(graph_technical_types())); ?> (<?php echo implode(", ", $premium_types); ?>)</td>
	</tr>
	<tr>
		<th><?php echo $welcome ? t("Your securities reports") : "<a href=\"" . htmlspecialchars(url_for('screenshots#screenshots_profile_summary')) . "\">" . ht("Your securities reports") . "</a>"; ?></th>
		<?php foreach (array('free', 'premium') as $type) { ?>
		<td class="<?php echo $type . " " . (get_premium_config('your_securities_' . $type) ? "yes" : "no"); ?>"><?php echo get_premium_config('your_securities_' . $type) ? "Y" : "-"; ?></td>
		<?php } ?>
	</tr>
	<tr>
		<th><?php echo t("Priority over free users"); ?></th>
		<td class="no">-</td>
		<td class="yes premium">Y</td>
	</tr>
	<tr>
		<th><?php echo t("Must login every"); ?></th>
		<td class="number"><?php echo plural("day", get_site_config('user_expiry_days')); ?></td>
		<td class="no premium"><?php echo t("Not necessary"); ?></td>
	</tr>
	<tr>
		<th><?php echo t("Data updated at least every"); ?></th>
		<td class="number"><?php echo plural("hour", get_site_config('refresh_queue_hours')); ?></td>
		<td class="number premium"><?php echo plural("hour", get_site_config('refresh_queue_hours_premium')); ?></td>
	</tr>
	<tr>
		<th><?php echo $welcome ? t("Notifications") : '<a href="' . htmlspecialchars(url_for('wizard_notifications')) . '">' . ht("Notifications") . '</a>' ?></th>
		<td class="number"><?php echo number_format(get_premium_config("summaries_free")); ?> (daily)</td>
		<td class="number premium"><?php echo number_format(get_premium_config("summaries_premium")); ?> (hourly)</td>
	</tr>
	<tr>
		<th><?php echo $welcome ? t("Finance Accounts") : '<a href="' . htmlspecialchars(url_for('finance_accounts')) . '">' . ht("Finance Accounts") . '</a>' ?></th>
		<td class="number"><?php echo number_format(get_premium_config("finance_accounts_free")); ?></td>
		<td class="number premium"><?php echo number_format(get_premium_config("finance_accounts_premium")); ?></td>
	</tr>
	<tr>
		<th><?php echo $welcome ? t("Finance Categories") : '<a href="' . htmlspecialchars(url_for('finance_categories')) . '">' . ht("Finance Categories") . '</a>' ?></th>
		<td class="number"><?php echo number_format(get_premium_config("finance_categories_free")); ?></td>
		<td class="number premium"><?php echo number_format(get_premium_config("finance_categories_premium")); ?></td>
	</tr>
	<tr>
		<th><?php echo t("Export transactions to CSV"); ?></th>
		<td class="no">-</td>
		<td class="yes premium">Y</td>
	</tr>
	<tr>
		<th><a href="<?php echo htmlspecialchars(url_for('vote_coins')); ?>"><?php echo t("New currency votes"); ?></a></th>
		<td class="number"><?php echo plural("vote", 1 * get_site_config('vote_coins_multiplier')); ?></td>
		<td class="number premium"><?php echo plural("vote", get_site_config('premium_user_votes') * get_site_config('vote_coins_multiplier')); ?></td>
	</tr>
	<tr>
		<th><a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'graph_refresh'))); ?>"><?php echo t("Live graph updates"); ?></a></th>
		<td class="number"><?php echo plural("minute", get_site_config('graph_refresh_free')); ?></td>
		<td class="number premium"><?php echo plural("minute", get_site_config('graph_refresh_premium')); ?></td>
	</tr>
	<?php if ($welcome) { ?>
	<tr class="payment">
		<th></th>
		<td class="free">
			<form action="<?php echo htmlspecialchars(url_for('user')); ?>" method="get">
			<input type="submit" value="<?php echo ht("Continue"); ?>">
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
