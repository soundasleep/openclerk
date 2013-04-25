<?php

if (!isset($account_data['titles'])) {
	$account_data['titles'] = $account_data['title'] . "s";
}

// process add/delete
if (require_post("add", false)) {
	$query = "";
	$args = array();
	foreach ($account_data['inputs'] as $key => $data) {
		$callback = $data['callback'];
		if (!$callback(require_post($key))) {
			$errors[] = "That is not a valid " . $data['title'];
			break;
		} else {
			$query .= ", $key=?";
			$args[] = require_post($key);
		}
	}
	if (!$errors) {
		// we don't care if the address already exists
		$q = db()->prepare("INSERT INTO " . $account_data['table'] . " SET user_id=?, title=? $query");
		$full_args = array_join(array(user_id(), require_post("title", false)), $args);
		$q->execute($full_args);
		$messages[] = "Added new " . htmlspecialchars($account_data['title']) . " <i>" . htmlspecialchars(require_post("title", "(untitled)")) . "</i>.";

		// redirect to GET
		set_temporary_messages($messages);
		redirect(url_for($account_data['url']));
	}
}

if (require_post("delete", false) && require_post("id", false)) {
	$q = db()->prepare("DELETE FROM " . $account_data['table'] . " WHERE id=? AND user_id=?");
	$q->execute(array(require_post("id"), user_id()));

	// also delete old address balances, since we won't be able to use them any more
	$q = db()->prepare("DELETE FROM balances WHERE account_id=? AND user_id=? AND exchange=?");
	$q->execute(array(require_post("id"), user_id(), $data['exchange']));

	$messages[] = "Removed " . htmlspecialchars($account_data['title']) . ".";

	// redirect to GET
	set_temporary_messages($messages);
	redirect(url_for($account_data['url']));
}

// get all of our accounts
$accounts = array();

$q = db()->prepare("SELECT * FROM " . $account_data['table'] . "
	WHERE user_id=? ORDER BY title ASC");
$q->execute(array(user_id()));
$accounts = $q->fetchAll();

page_header("Your Accounts: " . $account_data['titles'], "page_" . $account_data['url']);

?>

<p>
<a href="<?php echo htmlspecialchars(url_for('accounts')); ?>">&lt; Back to Your Accounts</a>
</p>

<h1>Your <?php echo htmlspecialchars($account_data['titles']); ?></h1>

<table class="standard standard_account_list">
<thead>
	<tr>
		<th>Title</th>
		<th>Added</th>
		<th>Last checked</th>
		<th>Balances</th>
		<th></th>
	</tr>
</thead>
<tbody>
<?php foreach ($accounts as $a) {
	// an account may have multiple currency balances
	$q = db()->prepare("SELECT * FROM balances WHERE user_id=? AND account_id=? AND exchange=? AND is_recent=1 ORDER BY currency ASC");
	$q->execute(array(user_id(), $a['id'], $account_data['exchange']));
	$balances = array();
	$last_updated = null;
	while ($balance = $q->fetch()) {
		$balances[$balance['currency']] = $balance['balance'];
		$last_updated = $balance['created_at'];
	}

	// was the last request successful?
	$q = db()->prepare("SELECT * FROM jobs WHERE user_id=? AND arg_id=? AND job_type=? ORDER BY id DESC");
	$q->execute(array(user_id(), $a['id'], $account_data['exchange']));
	$job = $q->fetch();
	if (!$last_updated && $job) {
		$last_updated = $job['executed_at'];
	}
?>
	<tr>
		<td><?php echo $a['title'] ? htmlspecialchars($a['title']) : "<i>untitled</i>"; ?></td>
		<td><?php echo recent_format_html($a['created_at']); ?></td>
		<td<?php if ($job) echo " class=\"" . ($job['is_error'] ? "job_error" : "job_success") . "\""; ?>>
			<?php echo recent_format_html($last_updated); ?>
		</td>
		<td><?php
			$had_balance = false;
			echo "<ul>";
			foreach ($balances as $c => $value) {
				if ($value != 0) {
					$had_balance = true;
					echo "<li>" . currency_format($c, $value, 4) . "</li>\n";
				}
			}
			echo "</ul>";
			if (!$had_balance) echo "-";
		?></td>
		<td>
			<form action="<?php echo htmlspecialchars(url_for($account_data['url'])); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
				<input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm('Are you sure you want to remove this account?');">
			</form>
		</td>
	</tr>
<?php } ?>
	<tr>
		<td colspan="5">
			<form action="<?php echo htmlspecialchars(url_for($account_data['url'])); ?>" method="post">
				<table class="form">
				<tr>
					<th><label for="title">Title:</label></th>
					<td><input id="title" type="text" name="title" size="18" maxlength="64" value="<?php echo htmlspecialchars(require_post("title", "")); ?>"> (optional)</td>
				</tr>
				<?php foreach ($account_data['inputs'] as $key => $data) { ?>
				<tr>
					<th><label for="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($data['title']); ?>:</label></th>
					<td><input id="<?php echo htmlspecialchars($key); ?>" type="text" name="<?php echo htmlspecialchars($key); ?>"
						size="48" maxlength="64" value="<?php echo htmlspecialchars(require_post($key, "")); ?>"></td>
				</tr>
				<?php } ?>
				<tr>
					<td colspan="2" class="buttons">
						<input type="submit" name="add" value="Add account" class="add">
					</td>
				</tr>
				</table>
			</form>
		</td>
	</tr>
</tbody>
</table>
