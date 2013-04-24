<?php

require("inc/global.php");

require("layout/templates.php");
page_header("Home", "page_home", false, array('common_js' => true));

?>
<h1><?php echo htmlspecialchars(get_site_config('site_name')); ?></h1>

<p>
Welcome to <b><?php echo htmlspecialchars(get_site_config('site_name')); ?></b>.
</p>

<h2>Supported exchanges</h2>

<?php
$exchange_data = array(
	"BitNZ" => array('nzd/btc'),
	"BTC-E" => array('btc/ltc', 'usd/btc', 'usd/ltc', 'btc/nmc'),
	"Mt.Gox" => array('usd/btc'),
);

// summarise
$all_currencies = array();
foreach ($exchange_data as $exchange => $pairs) {
	foreach ($pairs as $p) {
		$all_currencies[$p] = $p;
	}
}
?>
<table>
<thead>
	<tr>
		<th>Exchange</th>
		<?php foreach ($all_currencies as $p) { ?>
		<th><?php echo htmlspecialchars($p); ?></th>
		<?php } ?>
	</tr>
</thead>
<tbody>
	<?php foreach ($exchange_data as $exchange => $pairs) { ?>
	<tr>
		<td><?php echo htmlspecialchars($exchange); ?></td>
		<?php foreach ($all_currencies as $p) { ?>
		<?php echo in_array($p, $pairs) ? "<td class=\"yes\">Y</td>" : "<td class=\"no\"></td>"; ?>
		<?php } ?>
	</tr>
	<?php } ?>
</tbody>
</table>

<h2>Supported balances</h2>

<?php
$balances_data = array(
	"Generic API" => array('btc', 'ltc', 'nmc', 'nzd', 'usd'),
	"BTC-E" => array('btc', 'ltc', 'nmc', 'usd'),
	"Pool-x.eu" => array('ltc'),
);

// summarise
$all_currencies = array();
foreach ($balances_data as $exchange => $currencies) {
	foreach ($currencies as $p) {
		$all_currencies[$p] = $p;
	}
}
?>
<table>
<thead>
	<tr>
		<th>Source</th>
		<?php foreach ($all_currencies as $p) { ?>
		<th><?php echo htmlspecialchars($p); ?></th>
		<?php } ?>
	</tr>
</thead>
<tbody>
	<?php foreach ($balances_data as $exchange => $currencies) { ?>
	<tr>
		<td><?php echo htmlspecialchars($exchange); ?></td>
		<?php foreach ($all_currencies as $p) { ?>
		<?php echo in_array($p, $currencies) ? "<td class=\"yes\">Y</td>" : "<td class=\"no\"></td>"; ?>
		<?php } ?>
	</tr>
	<?php } ?>
</tbody>
</table>

<h2>Supported addresses</h2>

<?php
$addresses_data = array(
	"Blockchain" => array('btc'),
);

// summarise
$all_currencies = array();
foreach ($addresses_data as $exchange => $currencies) {
	foreach ($currencies as $p) {
		$all_currencies[$p] = $p;
	}
}
?>
<table>
<thead>
	<tr>
		<th>Source</th>
		<?php foreach ($all_currencies as $p) { ?>
		<th><?php echo htmlspecialchars($p); ?></th>
		<?php } ?>
	</tr>
</thead>
<tbody>
	<?php foreach ($addresses_data as $exchange => $currencies) { ?>
	<tr>
		<td><?php echo htmlspecialchars($exchange); ?></td>
		<?php foreach ($all_currencies as $p) { ?>
		<?php echo in_array($p, $currencies) ? "<td class=\"yes\">Y</td>" : "<td class=\"no\"></td>"; ?>
		<?php } ?>
	</tr>
	<?php } ?>
</tbody>
</table>

<?php
page_footer();
