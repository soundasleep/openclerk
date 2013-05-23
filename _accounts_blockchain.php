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

	$callback = $account_data['callback'];
	if (!$callback($address)) {
		$errors[] = "'" . htmlspecialchars($address) . "' is not a valid " . htmlspecialchars($account_data['title']) . ".";
	} else if (!can_user_add($user, $account_data['premium_group'])) {
		$errors[] = "Cannot add " . htmlspecialchars($account_data['title']) . ": too many existing addresses." .
				($user['is_premium'] ? "" : " To add more addresses, upgrade to a <a href=\"" . htmlspecialchars(url_for('premium')) . "\">premium account</a>.");
	} else {
		// we don't care if the address already exists
		$q = db()->prepare("INSERT INTO " . $account_data['table'] . " SET user_id=?, address=?, currency=?");
		$q->execute(array(user_id(), $address, $account_data['currency']));
		$address_callback = $account_data['address_callback'];
		$messages[] = "Added new " . htmlspecialchars($account_data['title']) . " " . $address_callback($address) . ". Balances from this address will be retrieved shortly.";

		// redirect to GET
		set_temporary_messages($messages);
		redirect(url_for($account_data['url']));
	}
}

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

<h1>Your <?php echo capitalize(htmlspecialchars($account_data['titles'])); ?></h1>

<span style="display:none;" id="sort_buttons_template">
<!-- heading sort buttons -->
<span class="sort_up" title="Sort ascending">Asc</span>
<span class="sort_down" title="Sort descending">Desc</span>
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

<p>
<form action="<?php echo htmlspecialchars(url_for($account_data['url'])); ?>" method="post">
<table class="standard">
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
</div>

<?php
page_footer();
