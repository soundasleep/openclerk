<div class="tabs" id="tabs_home">
	<ul class="tab_list">
		<?php /* each <li> must not have any whitespace between them otherwise whitespace will appear when rendered */ ?>
		<li id="tab_home_exchanges"><?php echo ht("Exchanges"); ?></li><li id="tab_home_pools"><?php echo ht("Mining Pools"); ?></li><li id="tab_home_wallets"><?php echo ht("Wallets"); ?></li><li id="tab_home_securities"><?php echo ht("Securities"); ?></li><li id="tab_home_addresses"><?php echo ht("Addresses"); ?></li><li id="tab_home_updates"><?php echo ht("Updates"); ?></li>
	</ul>

	<ul class="tab_groups">
		<li id="tab_home_exchanges_tab">

<h2><?php echo ht("Supported exchanges"); ?></h2>

<?php
$exchange_data = get_exchange_pairs();
$all_currencies = get_all_currencies();
?>
<table class="supported_exchanges">
<thead>
	<tr>
		<th>Exchange</th>
		<?php foreach ($all_currencies as $p) { ?>
		<th class="currency">
			<span class="currency_name_<?php echo htmlspecialchars($p); ?>" title="<?php echo htmlspecialchars(get_currency_name($p)); ?>"><?php echo htmlspecialchars(get_currency_abbr($p)); ?></span>
			<?php if (in_array($p, get_new_supported_currencies())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?>
		</th>
		<?php } ?>
	</tr>
</thead>
<tbody>
	<?php foreach ($exchange_data as $exchange => $pairs) { ?>
	<tr>
		<td>
			<?php echo htmlspecialchars(get_exchange_name($exchange)); ?>
			<?php if (in_array($exchange, get_new_exchanges())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?>
		</td>
		<?php foreach ($all_currencies as $currency) {
			$exchange_support = array();
			foreach ($pairs as $p) {
				if ($p[0] == $currency || $p[1] == $currency) {
					$p[0] = get_currency_abbr($p[0]);
					$p[1] = get_currency_abbr($p[1]);
					$exchange_support[] = implode("/", $p);
				}
			}
			echo $exchange_support ? "<td class=\"yes\" title=\"" . htmlspecialchars(implode(", ", $exchange_support)) . "\">Y</td>" : "<td class=\"no\"></td>"; ?>
		<?php } ?>
	</tr>
	<?php } ?>
</tbody>
</table>

<div class="screenshots_group">
<ul class="screenshots">
	<li class="historical"><a href="<?php echo htmlspecialchars(url_for('historical')); ?>"><?php echo ht("Historical Data"); ?></a></li>
</ul>
</div>

		</li>
		<li id="tab_home_pools_tab" style="display:none;">

<h2><?php echo ht("Supported mining pools"); ?></h2>

<?php
$balances_data = get_supported_wallets_safe();

// currencies are always sorted in the same order regardless of get_supported_wallets() order
// assumes there are no supported currencies that don't have a wallet
$all_currencies = get_all_currencies();
$all_cryptocurrencies = get_all_cryptocurrencies();
$account_data_grouped = account_data_grouped();

// issue #52: calculate 'mining pools' and 'other wallets'
$all_mining_pools = array();
$all_wallets = array();
foreach ($account_data_grouped['Mining pools'] as $exchange => $data) {
	if ($data['disabled']) {
		continue;
	}
	if (isset($balances_data[$exchange])) {
		$all_mining_pools[$exchange] = $balances_data[$exchange];
	} else if (isset($data['title_key']) && $data['title_key'] && isset($balances_data[$data['title_key']])) {
		$all_mining_pools[$data['title_key']] = $balances_data[$data['title_key']];
	} else {
		$all_wallets[$exchange] = $balances_data[$exchange];
	}
}
foreach (array_merge($account_data_grouped['Exchanges'], $account_data_grouped['Securities'], $account_data_grouped['Other']) as $exchange => $data) {
	if (!isset($balances_data[$exchange])) {
		continue;
	}
	if ($data['disabled']) {
		continue;
	}
	$all_wallets[$exchange] = $balances_data[$exchange];
}
// sort alphabetically
uksort($all_wallets, 'sort_all_wallets');
function sort_all_wallets($a, $b) {
	if ($a == "generic") return 1;
	if ($b == "generic") return -1;
	return strcmp(get_exchange_name($a), get_exchange_name($b));
}
?>
<table class="supported_wallets">
<thead>
	<tr>
		<th><?php echo ht("Source"); ?></th>
		<?php foreach ($all_cryptocurrencies as $p) { ?>
		<th class="currency">
			<span class="currency_name_<?php echo htmlspecialchars($p); ?>" title="<?php echo htmlspecialchars(get_currency_name($p)); ?>"><?php echo htmlspecialchars(get_currency_abbr($p)); ?></span>
			<?php if (in_array($p, get_new_supported_currencies())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?>
		</th>
		<?php } ?>
		<th><?php echo ht("Hashrate"); ?></th>
	</tr>
</thead>
<tbody>
	<?php foreach ($all_mining_pools as $exchange => $currencies) { ?>
	<tr>
		<td><?php echo htmlspecialchars(get_exchange_name($exchange)); if (in_array($exchange, get_new_supported_wallets())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?></td>
		<?php foreach ($all_cryptocurrencies as $p) { ?>
		<?php echo in_array($p, $currencies) ? "<td class=\"yes\" title=\"" . htmlspecialchars(get_currency_abbr($p)) . "\">Y</td>" : "<td class=\"no\"></td>"; ?>
		<?php } ?>
		<?php echo in_array('hash', $currencies) ? "<td class=\"yes\" title=\"Hashrate\">Y</td>" : "<td class=\"no\"></td>"; ?>
	</tr>
	<?php } ?>
</tbody>
<tfoot>
	<tr>
		<th><?php echo ht("Source"); ?></th>
		<?php foreach ($all_cryptocurrencies as $p) { ?>
		<th class="currency">
			<span class="currency_name_<?php echo htmlspecialchars($p); ?>" title="<?php echo htmlspecialchars(get_currency_name($p)); ?>"><?php echo htmlspecialchars(get_currency_abbr($p)); ?></span>
			<?php if (in_array($p, get_new_supported_currencies())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?>
		</th>
		<?php } ?>
		<th>Hashrate</th>
	</tr>
</tfoot>
</table>

<div class="screenshots_group">
<ul class="screenshots">
	<li class="add_service"><a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'add_service'))); ?>"><?php echo ht("Add another mining pool..."); ?></a></li>
</ul>
</div>

		</li>
		<li id="tab_home_wallets_tab" style="display:none;">

<h2><?php echo ht("Supported wallets"); ?></h2>

<table class="supported_wallets">
<thead>
	<tr>
		<th><?php echo ht("Source"); ?></th>
		<?php foreach ($all_currencies as $p) { ?>
		<th class="currency">
			<span class="currency_name_<?php echo htmlspecialchars($p); ?>" title="<?php echo htmlspecialchars(get_currency_name($p)); ?>"><?php echo htmlspecialchars(get_currency_abbr($p)); ?></span>
			<?php if (in_array($p, get_new_supported_currencies())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?>
		</th>
		<?php } ?>
	</tr>
</thead>
<tbody>
	<?php foreach ($all_wallets as $exchange => $currencies) { ?>
	<tr>
		<td><?php echo htmlspecialchars(get_exchange_name($exchange)); if (in_array($exchange, get_new_supported_wallets())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?></td>
		<?php foreach ($all_currencies as $p) { ?>
		<?php echo in_array($p, $currencies) ? "<td class=\"yes\" title=\"" . htmlspecialchars(get_currency_abbr($p)) . "\">Y</td>" : "<td class=\"no\"></td>"; ?>
		<?php } ?>
	</tr>
	<?php } ?>
</tbody>
<tfoot>
	<tr>
		<th><?php echo ht("Source"); ?></th>
		<?php foreach ($all_currencies as $p) { ?>
		<th class="currency">
			<span class="currency_name_<?php echo htmlspecialchars($p); ?>" title="<?php echo htmlspecialchars(get_currency_name($p)); ?>"><?php echo htmlspecialchars(get_currency_abbr($p)); ?></span>
			<?php if (in_array($p, get_new_supported_currencies())) echo " <span class=\"new\">" . ht("new"). "</span>"; ?>
		</th>
		<?php } ?>
	</tr>
</tfoot>
</table>

<div class="screenshots_group">
<ul class="screenshots">
	<li class="add_service"><a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'add_service'))); ?>"><?php echo ht("Add another wallet..."); ?></a></li>
</ul>
</div>

		</li>
		<li id="tab_home_securities_tab" style="display:none;">

<?php
$security_exchange_data_temp = get_security_exchange_pairs();

// remove any exchanges that are disabled
$security_exchange_data = array();
foreach ($security_exchange_data_temp as $exchange => $pairs) {
	foreach (account_data_grouped() as $label => $data) {
		foreach ($data as $key => $value) {
			if ($key == $exchange && !$value['disabled']) {
				$security_exchange_data[$exchange] = $pairs;
			}
		}
	}
}

// summarise
$all_security_currencies = array();
foreach ($security_exchange_data as $exchange => $pairs) {
	foreach ($pairs as $p) {
		$all_security_currencies[$p] = $p;
	}
}
?>
<table class="supported_securities">
<thead>
	<tr>
		<th><?php echo ht("Securities"); ?></th>
		<?php foreach ($all_security_currencies as $p => $pairs) { ?>
		<th><span class="currency_name_<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars(get_currency_abbr($p)); ?></span></th>
		<?php } ?>
	</tr>
</thead>
<tbody>
	<?php foreach ($security_exchange_data as $exchange => $pairs) { ?>
	<tr>
		<td>
			<?php echo htmlspecialchars(get_exchange_name($exchange)); ?>
			<?php if (in_array($exchange, get_new_security_exchanges())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?>
		</td>
		<?php foreach ($all_security_currencies as $p) { ?>
		<?php echo in_array($p, $pairs) ? "<td class=\"yes\">Y</td>" : "<td class=\"no\"></td>"; ?>
		<?php } ?>
	</tr>
	<?php } ?>
</tbody>
</table>

<div class="screenshots_group">
<ul class="screenshots">
	<li class="historical"><a href="<?php echo htmlspecialchars(url_for('historical')); ?>"><?php echo ht("Historical Data"); ?></a></li>
	<li class="add_service"><a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'add_service'))); ?>"><?php echo ht("Add another exchange..."); ?></a></li>
</ul>
</div>

		</li>
		<li id="tab_home_addresses_tab" style="display:none;">

<h2><?php echo ht("Supported addresses"); ?></h2>

<?php
$addresses_data = get_blockchain_currencies();

// summarise
$all_currencies = array();
foreach ($addresses_data as $exchange => $currencies) {
	$all_currencies[$exchange] = array();
	foreach ($currencies as $p) {
		$all_currencies[$exchange][] = "<span class=\"currency_name_" . htmlspecialchars($p) . "\" title=\"" . htmlspecialchars(get_currency_name($p)) . "\">" . htmlspecialchars(get_currency_abbr($p)) . "</span>";
	}
}
?>
<table class="supported_addresses">
<thead>
	<tr>
		<th><?php echo ht("Explorer"); ?></th>
		<th><?php echo ht("Currencies"); ?></th>
	</tr>
</thead>
<tbody>
	<?php foreach ($addresses_data as $exchange => $currencies) { ?>
	<tr>
		<td><?php echo htmlspecialchars($exchange); ?></td>
		<td class="currency_list"><?php echo implode(", ", $all_currencies[$exchange]); ?></td>
	</tr>
	<?php } ?>
</tbody>
</table>

<div class="screenshots_group">
<ul class="screenshots">
	<li class="add_service"><a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'add_currency'))); ?>"><?php echo ht("Add another cryptocurrency..."); ?></a></li>
</ul>
</div>

		</li>
		<li id="tab_home_updates_tab" style="display:none;">

			<dl class="version-list">
				<?php require_template("versions"); ?>

				<dt>&nbsp;</dt>
				<dd class="last-version-item">
					<a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'versions'))); ?>">Full version history</a>
				</dd>

				<dt>&nbsp;</dt>
				<dd class="last-version-item version-updates">
				Visit the <a href="<?php echo htmlspecialchars(url_for(get_site_config('blog_link'))); ?>" target="_blank"><?php echo htmlspecialchars(get_site_config('site_name')); ?> blog</a>,
				or subscribe to <a href="https://twitter.com/cryptfolio" target="_blank" class="twitter">@cryptfolio</a>
				or <span class="help_name_groups"><a href="http://groups.google.com/group/<?php echo htmlspecialchars(get_site_config('google_groups_announce')); ?>" target="_blank">cryptfolio-announce</a></span>, for updates.
				</dd>
			</dl>

		</li>
	</ul>
</div>
<script type="text/javascript">
$(document).ready(function() {
	initialise_tabs('#tabs_home');
});
</script>
