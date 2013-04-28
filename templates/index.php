<h1><?php echo htmlspecialchars(get_site_config('site_name')); ?></h1>

<p>
Welcome to <b><?php echo htmlspecialchars(get_site_config('site_name')); ?></b>.
</p>

<h2>Supported exchanges</h2>

<?php
$exchange_data = get_exchange_pairs();

// summarise
$all_currencies = array();
foreach ($exchange_data as $exchange => $pairs) {
	foreach ($pairs as $p) {
		$all_currencies[implode('/', $p)] = $p;
	}
}
?>
<table>
<thead>
	<tr>
		<th>Exchange</th>
		<?php foreach ($all_currencies as $p => $pairs) { ?>
		<th><?php echo htmlspecialchars($p); ?></th>
		<?php } ?>
	</tr>
</thead>
<tbody>
	<?php foreach ($exchange_data as $exchange => $pairs) { ?>
	<tr>
		<td><?php echo htmlspecialchars(get_exchange_name($exchange)); ?></td>
		<?php foreach ($all_currencies as $p) { ?>
		<?php echo in_array($p, $pairs) ? "<td class=\"yes\">Y</td>" : "<td class=\"no\"></td>"; ?>
		<?php } ?>
	</tr>
	<?php } ?>
</tbody>
</table>

<?php
$security_exchange_data = get_security_exchange_pairs();

// summarise
$all_security_currencies = array();
foreach ($security_exchange_data as $exchange => $pairs) {
	foreach ($pairs as $p) {
		$all_security_currencies[$p] = $p;
	}
}
?>
<table>
<thead>
	<tr>
		<th>Securities</th>
		<?php foreach ($all_security_currencies as $p => $pairs) { ?>
		<th><?php echo htmlspecialchars($p); ?></th>
		<?php } ?>
	</tr>
</thead>
<tbody>
	<?php foreach ($security_exchange_data as $exchange => $pairs) { ?>
	<tr>
		<td><?php echo htmlspecialchars(get_exchange_name($exchange)); ?></td>
		<?php foreach ($all_security_currencies as $p) { ?>
		<?php echo in_array($p, $pairs) ? "<td class=\"yes\">Y</td>" : "<td class=\"no\"></td>"; ?>
		<?php } ?>
	</tr>
	<?php } ?>
</tbody>
</table>

<h2>Supported balances</h2>

<?php
$balances_data = get_supported_wallets();

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
$addresses_data = get_blockchain_currencies();

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
