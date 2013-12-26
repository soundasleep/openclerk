<?php

if (!isset($account_data['titles'])) {
	$account_data['titles'] = $account_data['title'] . "s";
}

// get all of our accounts
$accounts = array();

$q = db()->prepare("SELECT
		addresses.id,
		addresses.address,
		addresses.created_at,
		addresses.user_id,
		addresses.title,
		address_balances.created_at AS last_updated,
		address_balances.balance
	FROM addresses
	LEFT JOIN (SELECT * FROM address_balances WHERE user_id=? AND is_recent=1) AS address_balances ON addresses.id=address_balances.address_id
	WHERE addresses.user_id=? AND addresses.currency=? ORDER BY IF(ISNULL(title) OR title='', 'untitled', title) ASC");
$q->execute(array(user_id(), user_id(), $account_data['currency']));
$accounts = $q->fetchAll();

?>

<!--
<h2>Your <?php echo htmlspecialchars($account_data['titles']); ?></h2>
-->

<span style="display:none;" id="sort_buttons_template">
<!-- heading sort buttons -->
<span class="sort_up" title="Sort ascending">Asc</span><span class="sort_down" title="Sort descending">Desc</span>
</span>

<table class="standard standard_account_list">
<thead>
	<tr>
		<th>Title</th>
		<th>Address</th>
		<th>Added</th>
		<th class="job_status">Last checked</th>
		<th>Balance</th>
		<th></th>
	</tr>
</thead>
<tbody>
<?php
$count = 0;
foreach ($accounts as $a) {
	$count++;
	$last_updated = $a['last_updated'];

	// was the last request successful?
	if ($a['balance'] === null) {
		$q = db()->prepare("SELECT jobs.*, uncaught_exceptions.message FROM jobs
			LEFT JOIN uncaught_exceptions ON uncaught_exceptions.job_id=jobs.id
			WHERE user_id=? AND arg_id=? AND job_type=? AND is_executed=1 AND jobs.is_recent=1
			ORDER BY jobs.id DESC LIMIT 1");
		$q->execute(array(user_id(), $a['id'], $account_data['job_type']));
		$job = $q->fetch();
		if (!$last_updated && $job) {
			$last_updated = $job['executed_at'];
		}
	}
?>
	<tr class="<?php echo $count % 2 == 0 ? "odd" : "even"; ?>">
		<td id="account<?php echo htmlspecialchars($a['id']); ?>" class="title">
			<span><?php echo $a['title'] ? htmlspecialchars($a['title']) : "<i>untitled</i>"; ?></span>
			<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses_post')); ?>" method="post" style="display:none;">
			<input type="text" name="title" value="<?php echo htmlspecialchars($a['title']); ?>">
			<input type="submit" value="Save">
			<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
			<input type="hidden" name="currency" value="<?php echo htmlspecialchars($account_data['currency']); ?>">
			</form>
		</td>
		<td><?php $address_callback = $account_data['address_callback']; echo $address_callback($a['address']); ?></td>
		<td><?php echo recent_format_html($a['created_at']); ?></td>
		<td class="job_status <?php if ($job) { echo $job['is_error'] ? "job_error" : "job_success"; } ?>">
			<?php echo recent_format_html($last_updated); ?>
			<?php if ($job && $job['message']) { ?>
			: <?php echo htmlspecialchars($job['message']); ?>
			<?php } ?>
		</td>
		<td><?php echo $a['balance'] === null ? "-" : currency_format($account_data['currency'], $a['balance']); ?></td>
		<td>
			<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses_post')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
				<input type="hidden" name="currency" value="<?php echo htmlspecialchars($account_data['currency']); ?>">
				<input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm('Are you sure you want to remove this address?');">
			</form>
		</td>
	</tr>
<?php } ?>
<?php if (!$accounts) { ?>
	<tr><td colspan="6"><i>(No addresses defined.)</i></td></tr>
<?php } ?>
</tbody>
</table>

<div class="columns2">
<div class="column">

<h2>Add new <?php echo htmlspecialchars($account_data['title']); ?></h1>

<p>
<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses_post')); ?>" method="post">
<table class="standard">
<tr>
	<th><label for="address">Title:</label></th>
	<td><input type="text" name="title" size="18" maxlength="64" value="<?php echo htmlspecialchars(require_post("title", "")); ?>"> (optional)</td>
</tr>
<tr>
	<th><label for="address"><?php echo htmlspecialchars($account_data['title']); ?>:</label></th>
	<td><input type="text" name="address" size="36" maxlength="36" value="<?php echo htmlspecialchars(require_post("address", "")); ?>"></td>
</tr>
<tr>
	<td colspan="2" class="buttons">
	<input type="hidden" name="currency" value="<?php echo htmlspecialchars($account_data['currency']); ?>">
	<input type="submit" name="add" value="Add address" class="add">
	</td>
</tr>
</table>
</form>
</p>

</div>
<div class="column">

<h2>Add multiple <?php echo htmlspecialchars($account_data['titles']); ?></h1>

<p>
<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses_post')); ?>" method="post">
<table class="standard">
<tr>
	<th><label for="address">Title:</label></th>
	<td><input type="text" name="title" size="18" maxlength="64" value="<?php echo htmlspecialchars(require_post("title", "")); ?>"> (optional)</td>
</tr>
<tr>
	<th><label for="address"><?php echo htmlspecialchars($account_data['titles']); ?>:</label></th>
	<td><textarea name="addresses" rows="5" cols="36"><?php echo htmlspecialchars(require_post("addresses", "")); ?></textarea><br><small>(One per line.)</small></td>
</tr>
<tr>
	<td colspan="2" class="buttons">
	<input type="hidden" name="currency" value="<?php echo htmlspecialchars($account_data['currency']); ?>">
	<input type="submit" name="add" value="Add addresses" class="add">
	</td>
</tr>
</table>
</form>
</p>

<hr>

<h2>Upload <?php echo htmlspecialchars($account_data['client']); ?> CSV</h2>

<form action="<?php echo htmlspecialchars(url_for("wizard_accounts_addresses_post")); ?>" method="post" enctype="multipart/form-data">
<table class="standard csv-upload">
<tr>
	<th><label for="address">CSV File:</label></th>
	<td>
		<input type="file" name="csv" accept="text/*">
		<input type="hidden" name="MAX_FILE_SIZE" value="131072">
	</td>
</tr>
<tr>
	<td colspan="2" class="buttons">
	<input type="hidden" name="currency" value="<?php echo htmlspecialchars($account_data['currency']); ?>">
	<input type="submit" name="add" value="Upload CSV" class="add">
<?php if (isset($account_data['csv_kb'])) { ?>
		<div class="help">
		<a href="<?php echo htmlspecialchars(url_for('kb', array('q' => $account_data['csv_kb']))); ?>">How do I upload a <?php echo htmlspecialchars($account_data['client']); ?> CSV file?</a>
		</div>
<?php } ?>
	</td>
</tr>
</table>
</form>


</div>
</div>

<div style="clear:both;"></div>