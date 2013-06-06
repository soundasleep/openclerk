<?php

$user = get_user(user_id());
require_user($user);

$messages = array();
$errors = array();

if (!isset($account_data['titles'])) {
	$account_data['titles'] = $account_data['title'] . "s";
}

// process edit
if (require_post("title", false) !== false && require_post("id", false)) {
	$id = require_post("id");
	$title = require_post("title");

	if (!is_valid_title($title)) {
		$errors[] = "'" . htmlspecialchars($title) . "' is not a valid " . htmlspecialchars($account_data['title']) . " title.";
	} else {
		$q = db()->prepare("UPDATE " . $account_data['table'] . " SET title=? WHERE user_id=? AND id=?");
		$q->execute(array($title, user_id(), $id));
		$messages[] = "Updated " . htmlspecialchars($account_data['title']) . " title.";

		// redirect to GET
		set_temporary_messages($messages);
		redirect(url_for($account_data['url']));
	}

}

// process add/delete
if (require_post("add", false) && require_post("address", false)) {
	$address = trim(require_post("address"));
	$title = trim(require_post("title", false));

	$callback = $account_data['callback'];
	if (!$callback($address)) {
		$errors[] = "'" . htmlspecialchars($address) . "' is not a valid " . htmlspecialchars($account_data['title']) . ".";
	} else if (!is_valid_title($title)) {
		$errors[] = "'" . htmlspecialchars($title) . "' is not a valid " . htmlspecialchars($account_data['title']) . " title.";
	} else if (!can_user_add($user, $account_data['premium_group'])) {
		$errors[] = "Cannot add " . htmlspecialchars($account_data['title']) . ": too many existing addresses." .
				($user['is_premium'] ? "" : " To add more addresses, upgrade to a <a href=\"" . htmlspecialchars(url_for('premium')) . "\">premium account</a>.");
	} else {
		// we don't care if the address already exists
		$q = db()->prepare("INSERT INTO " . $account_data['table'] . " SET user_id=?, address=?, currency=?, title=?");
		$q->execute(array(user_id(), $address, $account_data['currency'], $title));
		$address_callback = $account_data['address_callback'];
		$messages[] = "Added new " . htmlspecialchars($account_data['title']) . " " . $address_callback($address) . ". Balances from this address will be retrieved shortly.";

		// redirect to GET
		set_temporary_messages($messages);
		redirect(url_for($account_data['url']));
	}
}

// process delete
if (require_post("delete", false) && require_post("id", false)) {
	$q = db()->prepare("DELETE FROM " . $account_data['table'] . " WHERE id=? AND user_id=?");
	$q->execute(array(require_post("id"), user_id()));

	// also delete old address balances, since we won't be able to use them any more
	$q = db()->prepare("DELETE FROM address_balances WHERE address_id=? AND user_id=?");
	$q->execute(array(require_post("id"), user_id()));

	$messages[] = "Removed " . htmlspecialchars($account_data['title']) . " ID " . htmlspecialchars(require_post("id")) . ".";

	// redirect to GET
	set_temporary_messages($messages);
	redirect(url_for($account_data['url']));
}

// process file upload
if (isset($_FILES['csv'])) {
	try {
		// throws a BlockedException if this IP has requested this too many times recently
		check_heavy_request();
	} catch (BlockedException $e) {
		$errors[] = $e->getMessage();
		set_temporary_errors($errors);
		redirect(url_for($account_data['url']));
	}

	$invalid_addresses = 0;
	$updated_titles = 0;
	$existing_addresses = 0;
	$new_addresses = 0;
	$limited_addresses = 0;

	// get all of our addresses for quick reading
	$addresses = array();
	$q = db()->prepare("SELECT * FROM " . $account_data['table'] . " WHERE user_id=? AND currency=?");
	$q->execute(array(user_id(), $account_data['currency']));
	while ($a = $q->fetch()) {
		$addresses[$a['address']] = $a;
	}

	// lets read this file in as CSV
	// we don't store this CSV file on the server
	$fp = fopen($_FILES['csv']['tmp_name'], "r");
	while ($fp && ($row = fgetcsv($fp, 1000, ",")) !== false) {
		if (count($row) < 2)
			continue;	// also happens for invalid .csv files
		if ($row[1] == "Address")
			continue;

		// otherwise, row[0] should be a label, and row[1] should be an address
		if (!$account_data['callback']($row[1])) {
			$invalid_addresses++;
		} else {
			// do we already have this address?
			if (isset($addresses[$row[1]])) {
				$existing_addresses++;
				// do we need to update the title?
				if ($addresses[$row[1]]['title'] != $row[0]) {
					$q = db()->prepare("UPDATE " . $account_data['table'] . " SET title=? WHERE user_id=? AND id=?");
					$q->execute(array($row[0], user_id(), $addresses[$row[1]]['id']));
					$addresses[$row[1]]['title'] = $row[0];
					$updated_titles++;
				}
			} else {
				// we need to insert in a new address
				if (!can_user_add($user, $account_data['premium_group'], $new_addresses + 1)) {
					$limited_addresses++;

				} else {
					$q = db()->prepare("INSERT INTO " . $account_data['table'] . " SET user_id=?, address=?, currency=?, title=?");
					$q->execute(array(user_id(), $row[1], $account_data['currency'], $row[0]));
					$addresses[$row[1]] = array('id' => db()->lastInsertId(), 'title' => $row[1]);
					$new_addresses++;
				}
			}
		}
	}

	// update messages
	if ($invalid_addresses) {
		$errors[] = number_format($invalid_addresses) . " addresses were invalid and were not added.";
	}
	if ($limited_addresses) {
		$errors[] = "Could not add " . number_format($limited_addresses) . " addresses: too many existing addresses." .
			($user['is_premium'] ? "" : " To add more addresses, upgrade to a <a href=\"" . htmlspecialchars(url_for('premium')) . "\">premium account</a>.");
	}
	$messages[] = "Added " . plural($new_addresses, "new address", "new addresses") . " and
		updated " . plural($existing_addresses, "exsting address", "existing addresses") . ".";

	// redirect to GET
	set_temporary_messages($messages);
	set_temporary_errors($errors);
	redirect(url_for($account_data['url']));
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
	WHERE addresses.user_id=? AND addresses.currency=? ORDER BY address ASC");
$q->execute(array(user_id(), user_id(), $account_data['currency']));
$accounts = $q->fetchAll();

page_header("Your Accounts: " . capitalize($account_data['titles']), "page_" . $account_data['url'], array('jquery' => true, 'js' => 'accounts'));

?>

<div class="page_accounts">
<div class="tip tip_float">
As a <?php echo $user['is_premium'] ? "premium user" : "<a href=\"" . htmlspecialchars(url_for('premium')) . "\">free user</a>"; ?>, your
<?php echo htmlspecialchars($account_data['titles']); ?> should be updated
at least once every <?php echo plural(get_premium_value($user, "refresh_queue_hours"), 'hour'); ?>.
</div>

<p class="backlink">
<a href="<?php echo htmlspecialchars(url_for('accounts')); ?>">&lt; Back to Your Accounts</a>
</p>

<h1>Your <?php echo htmlspecialchars($account_data['titles']); ?></h1>

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
		<th>Last checked</th>
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
	$q = db()->prepare("SELECT jobs.*, uncaught_exceptions.message FROM jobs
		LEFT JOIN uncaught_exceptions ON uncaught_exceptions.job_id=jobs.id
		WHERE user_id=? AND arg_id=? AND job_type=? AND is_executed=1
		ORDER BY jobs.id DESC LIMIT 1");
	$q->execute(array(user_id(), $a['id'], $account_data['job_type']));
	$job = $q->fetch();
	if (!$last_updated && $job) {
		$last_updated = $job['executed_at'];
	}
?>
	<tr class="<?php echo $count % 2 == 0 ? "odd" : "even"; ?>">
		<td id="account<?php echo htmlspecialchars($a['id']); ?>" class="title">
			<span><?php echo $a['title'] ? htmlspecialchars($a['title']) : "<i>untitled</i>"; ?></span>
			<form action="<?php echo htmlspecialchars(url_for($account_data['url'])); ?>" method="post" style="display:none;">
			<input type="text" name="title" value="<?php echo htmlspecialchars($a['title']); ?>">
			<input type="submit" value="Save">
			<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
			</form>
		</td>
		<td><?php $address_callback = $account_data['address_callback']; echo $address_callback($a['address']); ?></td>
		<td><?php echo recent_format_html($a['created_at']); ?></td>
		<td<?php if ($job) echo " class=\"" . ($job['is_error'] ? "job_error" : "job_success") . "\""; ?>>
			<?php echo recent_format_html($last_updated); ?>
			<?php if ($job['message']) { ?>
			: <?php echo htmlspecialchars($job['message']); ?>
			<?php } ?>
		</td>
		<td><?php echo $a['balance'] === null ? "-" : currency_format($account_data['currency'], $a['balance']); ?></td>
		<td>
			<form action="<?php echo htmlspecialchars(url_for($account_data['url'])); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
				<input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm('Are you sure you want to remove this address?');">
			</form>
		</td>
	</tr>
<?php } ?>
<?php if (!$accounts) { ?>
	<tr><td colspan="5"><i>(No addresses defined.)</i></td></tr>
<?php } ?>
</tbody>
</table>

<h2>Add new <?php echo htmlspecialchars($account_data['title']); ?></h1>

<p>
<form action="<?php echo htmlspecialchars(url_for($account_data['url'])); ?>" method="post">
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
	<input type="submit" name="add" value="Add address" class="add">
	</td>
</tr>
</table>
</form>
</p>

<?php if (isset($account_data['client'])) { ?>
<h2>Upload <?php echo htmlspecialchars($account_data['client']); ?> CSV</h2>

<div class="tip tip_float">
If you are using the default <?php echo htmlspecialchars($account_data['client']); ?> client, you can
use the "export" feature of the client to automatically populate your list of <?php echo htmlspecialchars($account_data['titles']); ?> using your existing address labels.
</div>

<p>
<form action="<?php echo htmlspecialchars(url_for($account_data['url'])); ?>" method="post" enctype="multipart/form-data">
<table class="standard">
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
	<br><small>Any invalid or duplicated addresses will be skipped.</small>
	</td>
</tr>
</table>
</form>
</p>

<div class="instructions_add">
<h2>Uploading a <?php echo htmlspecialchars($account_data['client']); ?> CSV file</h2>

<ol class="steps">
	<li>Open your <?php echo htmlspecialchars($account_data['client']); ?> client, and
		open the "Receive coins" tab.<br>
		<img src="img/accounts/<?php echo $account_data['step1']; ?>">

	<li>Click the "Export" button and save this CSV file to your computer. Once this CSV file has
		been exported, select the "Browse..." button above
		to locate and upload this file to <?php echo htmlspecialchars(get_site_config('site_name')); ?>.<br>
		<img src="img/accounts/<?php echo $account_data['step2']; ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo htmlspecialchars($account_data['client']); ?> CSV file?</h2>

<ul>
	<li>The <?php echo htmlspecialchars($account_data['client']); ?> client will only export your <i>public</i>
		<?php echo htmlspecialchars(get_currency_name($account_data['currency'])); ?> addresses. These addresses can only be used to retrieve
		address balances; it is not possible to perform transactions using a public address.</li>
</ul>
</div>
<?php } ?>

</div>

<?php
page_footer();
