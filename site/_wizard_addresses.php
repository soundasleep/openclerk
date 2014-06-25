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

<?php require(__DIR__ . "/_sort_buttons.php"); ?>

<table class="standard standard_account_list">
<thead>
	<tr>
		<th class="title"><?php echo ht("Title"); ?></th>
		<th class="address"><?php echo ht("Address"); ?></th>
		<th class="added"><?php echo ht("Added"); ?></th>
		<th class="job_status"><?php echo ht("Checked"); ?></th>
		<th class="balance"><?php echo ht("Balance"); ?></th>
		<th class="transactions"><?php echo ht("Transactions"); ?></th>
		<th class="buttons"></th>
	</tr>
</thead>
<tbody>
<?php
$count = 0;
foreach ($accounts as $a) {
	$count++;
	$last_updated = $a['last_updated'];
	$job = false;

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
			<input type="submit" value="<?php echo ht("Save"); ?>">
			<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
			<input type="hidden" name="currency" value="<?php echo htmlspecialchars($account_data['currency']); ?>">
			</form>
		</td>
		<td class="address"><?php echo crypto_address($account_data['currency'], $a['address']); ?></td>
		<td class="added"><?php echo recent_format_html($a['created_at']); ?></td>
		<td class="job_status <?php if ($job) { echo $job['is_error'] ? "job_error" : "job_success"; } ?>">
			<?php echo recent_format_html($last_updated); ?>
			<?php if ($job && $job['message']) { ?>
			: <?php echo htmlspecialchars($job['message']); ?>
			<?php } ?>
		</td>
		<td class="balances"><?php echo $a['balance'] === null ? "-" : currency_format($account_data['currency'], $a['balance']); ?></td>
		<?php
		$q = db()->prepare("SELECT * FROM transaction_creators WHERE exchange=? AND account_id=?");
		$q->execute(array($account_data['job_type'], $a['id']));
		$creator = $q->fetch();
		$enabled = !$creator || !$creator['is_disabled'];

		$q = db()->prepare("SELECT COUNT(*) AS c FROM transactions WHERE user_id=? AND exchange=? AND account_id=?");
		$q->execute(array(user_id(), $account_data['job_type'], $a['id']));
		$transaction_count = $q->fetch();
		?>
		<td class="buttons transactions">
			<?php if ($enabled) { ?>
			<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses_post')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
				<input type="submit" name="disable_creator" value="<?php echo ht("Disable"); ?>" class="disable" onclick="return confirmCreatorDisable();" title="<?php echo ht("Disable transaction generation for this account"); ?>">
				<input type="hidden" name="currency" value="<?php echo htmlspecialchars($account_data['currency']); ?>">
				<input type="hidden" name="callback" value="wizard_accounts_addresses">
			</form>
			<?php } else { ?>
			<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses_post')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
				<input type="submit" name="enable_creator" value="<?php echo ht("Enable"); ?>" class="enable" title="Enable transaction generation for this account"); ?>">
				<input type="hidden" name="currency" value="<?php echo htmlspecialchars($account_data['currency']); ?>">
				<input type="hidden" name="callback" value="wizard_accounts_addresses">
			</form>
		<?php } ?>
			<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses_post')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
				<input type="submit" name="reset_creator" value="<?php echo ht("Reset"); ?>" class="reset" onclick="return confirmTransactionsReset();" title="<?php echo ht("Delete generated historical transactions and start again"); ?>">
				<input type="hidden" name="currency" value="<?php echo htmlspecialchars($account_data['currency']); ?>">
				<input type="hidden" name="callback" value="wizard_accounts_addresses">
			</form>
			<span class="transaction-count">
				<a href="<?php echo htmlspecialchars(url_for('your_transactions', array('exchange' => $account_data['job_type'], 'account_id' => $a['id']))); ?>" class="view-transactions" title="<?php echo ht("View historical transactions"); ?>">View</a>
				(<?php echo number_format($transaction_count['c']); ?>)
			</span>
		</td>
		<td class="buttons">
			<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses_post')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
				<input type="hidden" name="currency" value="<?php echo htmlspecialchars($account_data['currency']); ?>">
				<input type="submit" name="delete" value="<?php echo ht("Delete"); ?>" class="delete" onclick="return confirm('<?php echo ht("Are you sure you want to remove this address?"); ?>');" title="<?php echo ht("Delete this address"); ?>">
			</form>
		</td>
	</tr>
<?php } ?>
<?php if (!$accounts) { ?>
	<tr><td colspan="6"><i><?php echo ht("(No addresses defined.)"); ?></i></td></tr>
<?php } ?>
</tbody>
</table>

<div class="columns2">
<div class="column">

<h2><?php echo ht("Add new :title", array(':title' => $account_data['title'])); ?></h1>

<p>
<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses_post')); ?>" method="post">
<table class="standard">
<tr>
	<th><label for="address"><?php echo ht("Title:"); ?></label></th>
	<td><input type="text" name="title" size="18" maxlength="64" value="<?php echo htmlspecialchars(require_post("title", "")); ?>"> <?php echo ht("(optional)"); ?></td>
</tr>
<tr>
	<th><label for="address"><?php echo htmlspecialchars($account_data['title']); ?>:</label></th>
	<td><input type="text" name="address" size="36" maxlength="36" value="<?php echo htmlspecialchars(require_post("address", "")); ?>"></td>
</tr>
<tr>
	<td colspan="2" class="buttons">
	<input type="hidden" name="currency" value="<?php echo htmlspecialchars($account_data['currency']); ?>">
	<input type="submit" name="add" value="<?php echo ht("Add address"); ?>" class="add">
	</td>
</tr>
</table>
</form>
</p>

</div>
<div class="column">

<h2><?php echo ht("Add multiple :titles", array(':titles' => $account_data['titles'])); ?></h1>

<p>
<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses_post')); ?>" method="post">
<table class="standard">
<tr>
	<th><label for="address"><?php echo ht("Title:"); ?></label></th>
	<td><input type="text" name="title" size="18" maxlength="64" value="<?php echo htmlspecialchars(require_post("title", "")); ?>"> <?php echo ht("(optional)"); ?></td>
</tr>
<tr>
	<th><label for="address"><?php echo htmlspecialchars($account_data['titles']); ?>:</label></th>
	<td><textarea name="addresses" rows="5" cols="36"><?php echo htmlspecialchars(require_post("addresses", "")); ?></textarea><br><small><?php echo ht("(One address per line.)"); ?></small></td>
</tr>
<tr>
	<td colspan="2" class="buttons">
	<input type="hidden" name="currency" value="<?php echo htmlspecialchars($account_data['currency']); ?>">
	<input type="submit" name="add" value="<?php echo ht("Add addresses"); ?>" class="add">
	</td>
</tr>
</table>
</form>
</p>

<hr>

<h2><?php echo ht("Upload :client CSV", array(':client' => $account_data['client'])); ?></h2>

<form action="<?php echo htmlspecialchars(url_for("wizard_accounts_addresses_post")); ?>" method="post" enctype="multipart/form-data">
<table class="standard csv-upload">
<tr>
	<th><label for="address"><?php echo ht("CSV File:"); ?></label></th>
	<td>
		<input type="file" name="csv" accept="text/*">
		<input type="hidden" name="MAX_FILE_SIZE" value="131072">
	</td>
</tr>
<tr>
	<td colspan="2" class="buttons">
	<input type="hidden" name="currency" value="<?php echo htmlspecialchars($account_data['currency']); ?>">
	<input type="submit" name="add" value="<?php echo ht("Upload CSV"); ?>" class="add">
<?php if (isset($account_data['csv_kb'])) { ?>
		<div class="help">
		<a href="<?php echo htmlspecialchars(url_for('kb', array('q' => $account_data['csv_kb']))); ?>"><?php echo t("How do I upload a :client CSV file?", array(':client' => $account_data['client'])); ?></a>
		</div>
<?php } ?>
	</td>
</tr>
</table>
</form>


</div>
</div>

<div style="clear:both;"></div>
