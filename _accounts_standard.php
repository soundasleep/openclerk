<?php

// we will have set $account_data already
if (!isset($account_data)) {
	throw new Exception("account_data needs to be set");
}

if (!isset($account_data['title'])) {
	$account_data['title'] = get_exchange_name($account_data['exchange']) . " account";
}
$account_data['exchange_name'] = get_exchange_name($account_data['exchange']);

if (!isset($account_data['titles'])) {
	$account_data['titles'] = $account_data['title'] . "s";
}

if (!isset($account_data['display'])) {
	$account_data['display'] = array();
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
	if (!is_valid_title(require_post("title", false))) {
		$errors[] = "That is not a valid title.";
	}
	if (!can_user_add($user, $account_data['exchange'])) {
		$errors[] = "Cannot add " . $account_data['title'] . ": too many existing " . $account_data['titles'] . " for your account.<br>" .
				($user['is_premium'] ? "" : " To add more " . $account_data['titles'] . ", upgrade to a <a href=\"" . htmlspecialchars(url_for('premium')) . "\">premium account</a>.");
	}
	if (!$errors) {
		// we don't care if the address already exists
		$q = db()->prepare("INSERT INTO " . $account_data['table'] . " SET user_id=?, title=? $query");
		$full_args = array_join(array(user_id(), require_post("title", false)), $args);
		$q->execute($full_args);
		$title = htmlspecialchars(require_post("title", ""));
		if (!$title) $title = "<i>(untitled)</i>";
		$messages[] = "Added new " . htmlspecialchars($account_data['title']) . " <i>" . $title . "</i>. Balances from this account will be retrieved shortly.";

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

page_header("Your Accounts: " . $account_data['titles'], "page_" . $account_data['url'], array('jquery' => true, 'js' => 'accounts'));

?>

<div class="page_accounts">
<?php require("_accounts_tip.php"); ?>

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
		<?php foreach ($account_data['display'] as $key => $value) { ?>
			<th><?php echo htmlspecialchars($value['title']); ?></th>
		<?php } ?>
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
	$q = db()->prepare("SELECT jobs.*, uncaught_exceptions.message FROM jobs
		LEFT JOIN uncaught_exceptions ON uncaught_exceptions.job_id=jobs.id
		WHERE user_id=? AND arg_id=? AND job_type=? AND is_executed=1
		ORDER BY jobs.id DESC LIMIT 1");
	$q->execute(array(user_id(), $a['id'], $account_data['exchange']));
	$job = $q->fetch();
	if (!$last_updated && $job) {
		$last_updated = $job['executed_at'];
	}
?>
	<tr>
		<td id="account<?php echo htmlspecialchars($a['id']); ?>" class="title">
			<span><?php echo $a['title'] ? htmlspecialchars($a['title']) : "<i>untitled</i>"; ?></span>
			<form action="<?php echo htmlspecialchars(url_for($account_data['url'])); ?>" method="post" style="display:none;">
			<input type="text" name="title" value="<?php echo htmlspecialchars($a['title']); ?>">
			<input type="submit" value="Save">
			<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
			</form>
		</td>
		<?php foreach ($account_data['display'] as $key => $value) {
			$format_callback = $value['format']; ?>
			<td><?php echo $format_callback($a[$key]); ?></td>
		<?php } ?>
		<td><?php echo recent_format_html($a['created_at']); ?></td>
		<td<?php if ($job) echo " class=\"" . ($job['is_error'] ? "job_error" : "job_success") . "\""; ?>>
			<?php echo recent_format_html($last_updated); ?>
			<?php if ($job['message']) { ?>
			: <?php echo htmlspecialchars($job['message']); ?>
			<?php } ?>
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
<?php if (!$accounts) { ?>
	<tr><td colspan="<?php echo count($account_data['display']) + 5; ?>"><i>(No accounts defined.)</i></td></tr>
<?php } ?>
</tbody>
</table>

<h2>Add new <?php echo htmlspecialchars($account_data['title']); ?></h1>

<p>
<form action="<?php echo htmlspecialchars(url_for($account_data['url'])); ?>" method="post">
	<table class="standard">
	<tr>
		<th><label for="title">Title:</label></th>
		<td><input id="title" type="text" name="title" size="18" maxlength="64" value="<?php echo htmlspecialchars(require_post("title", "")); ?>"> (optional)</td>
	</tr>
	<?php foreach ($account_data['inputs'] as $key => $data) {
		$length = isset($data['length']) ? $data['length'] : 64; ?>
	<tr>
		<th><label for="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($data['title']); ?>:</label></th>
		<td>
			<?php if (isset($data['dropdown'])) { ?>
				<select id="<?php echo htmlspecialchars($key); ?>" name="<?php echo htmlspecialchars($key); ?>">
					<?php $options = $data['dropdown']();
					foreach ($options as $dkey => $dvalue) {
						echo "<option value=\"" . htmlspecialchars($dkey) . "\"" . (require_post($key, "") == $dkey ? " select" : "") . ">";
						echo htmlspecialchars($dvalue);
						echo "</option>\n";
					} ?>
				</select>
			<?php } else { ?>
				<input id="<?php echo htmlspecialchars($key); ?>" type="text" name="<?php echo htmlspecialchars($key); ?>"
					size="<?php echo htmlspecialchars($length * 2/3); ?>" maxlength="<?php echo htmlspecialchars($length); ?>" value="<?php echo htmlspecialchars(require_post($key, "")); ?>"></td>
			<?php } ?>
	</tr>
	<?php } ?>
	<tr>
		<td colspan="2" class="buttons">
			<input type="submit" name="add" value="Add account" class="add">
		</td>
	</tr>
	</table>
</form>
</p>
</div>