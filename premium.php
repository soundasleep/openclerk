<?php

/**
 * Display information about premium accounts.
 */

require("inc/global.php");

require("layout/templates.php");

$messages = array();
$errors = array();

page_header("Premium Accounts", "page_premium");

?>

<h1>Support <?php echo htmlspecialchars(get_site_config('site_name')); ?> with Premium Accounts</h1>

<p>
	You can support <?php echo htmlspecialchars(get_site_config('site_name')); ?> by purchasing a
	premium account with <?php
		$result = array();
		foreach (get_site_config('premium_currencies') as $currency) {
			$result[] = get_currency_name($currency);
		}
		for ($i = 0; $i < count($result) - 2; $i++) {
			echo $result[$i] . ", ";
		}
		for ($i = count($result) - 2; $i >= 0 && $i < count($result) - 1; $i++) {
			echo $result[$i] . " and ";
		}
		for ($i = count($result) - 1; $i >= 0 && $i < count($result); $i++) {
			echo $result[$i];
		}
		?> currencies. You will also get access to exclusive, premium-only functionality such as
	vastly increased limits on the number of addresses and accounts you may track at once,
	and advanced reporting and notification functionality.
</p>

<table class="standard">
<thead>
	<tr>
		<th>Feature</th>
		<th>Free account</th>
		<th>Premium account</th>
	</tr>
</thead>
<tbody>
	<?php
	$currencies = get_blockchain_currencies();
	$currencies = array_map('get_currency_name', $currencies);
	$predef = array(
		'addresses' => 'Tracked addresses (' . implode(", ", $currencies) . ')',
		'accounts' => 'Tracked accounts (BTC-E, Mt.Gox, ...)',
		'graph_pages' => 'Summary pages',
		'graphs_per_page' => 'Graphs per summary page',
		'summaries' => 'Currency summaries',
	);
	foreach ($predef as $key => $title) { ?>
	<tr>
		<th><?php echo $title; ?></th>
		<td><?php echo number_format(get_premium_config($key . "_free")); ?></td>
		<td><?php echo number_format(get_premium_config($key . "_premium")); ?></td>
	</tr>
	<?php } ?>
	<tr>
		<th>Data updated at least every</th>
		<td><?php echo plural(get_site_config('refresh_queue_hours'), 'hour', 'hours'); ?></td>
		<td><?php echo plural(get_site_config('refresh_queue_hours_premium'), 'hour', 'hours'); ?></td>
	</tr>
	<tr>
		<th>Advanced reporting functionality</th>
		<td>-</td>
		<td>Coming soon...</td>
	</tr>
	<tr>
		<th>Advanced notification functionality</th>
		<td>-</td>
		<td>Coming soon...</td>
	</tr>
</tbody>
</table>

<p>
	You may purchase or extend your premium account by logging into your
	<a href="<?php echo htmlspecialchars(url_for('user')); ?>">user account</a>, or
	by selecting the appropriate payment option below.
</p>

<?php require("_premium_prices.php"); ?>

<?php
page_footer();
