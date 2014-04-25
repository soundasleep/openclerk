<?php

/**
 * This page displays all of your hashrates with tabs and the current values of each
 * type of balance.
 */

require(__DIR__ . "/../inc/global.php");
require_login();

require(__DIR__ . "/../layout/templates.php");

$user = get_user(user_id());

require(__DIR__ . "/_profile_common.php");

page_header("Your Hashrates", "page_your_hashrates", array('js' => array('accounts'), 'class' => 'report_page'));

// get all pages
$q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=? AND is_removed=0 ORDER BY page_order ASC, id ASC");
$q->execute(array(user_id()));
$pages = $q->fetchAll();

// get all of our latest balances, ignoring currencies we're not interested in
$balances = array();
$last_updated = array();
require(__DIR__ . "/../graphs/util.php");
foreach (get_all_summary_currencies() as $cur => $summary) {
	$balances[$cur] = array();
}

$q = db()->prepare("SELECT * FROM hashrates WHERE user_id=? AND is_recent=1");
$q->execute(array(user_id()));
while ($balance = $q->fetch()) {
	if (isset($balances[$balance['currency']])) {
		if (!isset($balances[$balance['currency']][$balance['exchange']])) {
			$balances[$balance['currency']][$balance['exchange']] = 0;
		}
		$balances[$balance['currency']][$balance['exchange']] += demo_scale($balance['mhash']);
		$last_updated[$balance['exchange']] = $balance['created_at'];
	}
}

// remove empty currencies
$temp = array();
foreach ($balances as $cur => $data) {
	if ($data) {
		$temp[$cur] = $data;
	}
}
ksort($temp);
$balances = $temp;

// now print out tabs and tables for each currency

?>

<!-- page list -->
<?php
$page_id = -1;
$your_hashrates = true;
require(__DIR__ . "/_profile_pages.php");
?>

<span style="display:none;" id="sort_buttons_template">
<!-- heading sort buttons -->
<span class="sort_up" title="Sort ascending">Asc</span><span class="sort_down" title="Sort descending">Desc</span>
</span>

<div class="tabs" id="tabs_your_currencies">
	<ul class="tab_list">
		<?php
		/* each <li> must not have any whitespace between them otherwise whitespace will appear when rendered */
		foreach ($balances as $currency => $data) {
			echo '<li id="tab_currencies_' . htmlspecialchars($currency) . '"><span class="currency_name_' . htmlspecialchars($currency) . '">' . htmlspecialchars(get_currency_abbr($currency)) . '</span></li>';
		} ?>
	</ul>

	<ul class="tab_groups">
		<?php $first_tab = true;
		foreach ($balances as $currency => $data) { ?>
		<li id="tab_currencies_<?php echo htmlspecialchars($currency); ?>_tab"<?php echo $first_tab ? "" : " style=\"display:none;\""; ?>>

<table class="standard standard_account_list">
<thead>
	<tr>
		<th class="source">Source</th>
		<th class="updated">Last updated</th>
		<th class="balance default_sort_down">Balance</th>
	</tr>
</thead>
<tbody>
<?php
$count = 0;
$sum = 0;
foreach ($data as $exchange => $balance) {
	$sum += $balance; ?>
	<tr class="<?php echo $count % 2 == 0 ? "odd" : "even"; ?>">
		<td><?php echo htmlspecialchars(get_exchange_name($exchange)); ?></td>
		<td><?php echo recent_format_html($last_updated[$exchange]); ?></td>
		<td class="number"><?php echo number_format_html($balance, 4); ?> MH/s</td>
	</tr>
<?php 	$first_tab = false;
	} ?>
</tbody>
<tfoot>
	<tr>
		<th colspan="2">Total <?php echo htmlspecialchars(get_currency_name($currency)); ?></th>
		<th class="number"><?php echo number_format_html($sum, 4); ?> MH/s</th>
	</tr>
</tfoot>
</table>

		</li>
		<?php } ?>

		<?php if (!$balances) { ?>
		<li>
		Either you have not specified any accounts or addresses, or these addresses and accounts have not yet been updated by <?php echo htmlspecialchars(get_site_config('site_name')); ?>.<br>
		<a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">Add accounts and addresses</a>
		</li>
		<?php } ?>
	</ul>
</div>

<script type="text/javascript">
$(document).ready(function() {
	initialise_tabs('#tabs_your_currencies');
});
</script>

<?php

page_footer();
