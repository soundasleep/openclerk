<?php

/**
 * This page does the hard work of displaying what accounts a user currently has enabled.
 * We delegate adding/deleting accounts to each of the separate account pages.
 */

require("inc/global.php");
require_login();

require("layout/templates.php");

$user = get_user(user_id());
if (!$user) {
	throw new Exception("Could not find self user.");
}

$messages = array();
$errors = array();

// process add/delete
if (require_post("add", false) && require_post("api_key", false)) {
	if (!is_valid_poolx_apikey(require_post("api_key"))) {
		$errors[] = "That is not a valid API key.";
	} else {
		// we don't care if the address already exists
		$q = db()->prepare("INSERT INTO accounts_poolx SET user_id=?, api_key=?, title=?");
		$q->execute(array(user_id(), require_post("api_key"), require_post("title", false)));
		$messages[] = "Added new Pool-x account <i>" . htmlspecialchars(require_post("title", "(untitled)")) . "</i>.";

		// redirect to GET
		set_temporary_messages($messages);
		redirect(url_for('accounts_poolx'));
	}
}

if (require_post("delete", false) && require_post("id", false)) {
	$q = db()->prepare("DELETE FROM accounts_poolx WHERE id=? AND user_id=?");
	$q->execute(array(require_post("id"), user_id()));

	// also delete old address balances, since we won't be able to use them any more
	$q = db()->prepare("DELETE FROM balances WHERE account_id=? AND user_id=? AND exchange=?");
	$q->execute(array(require_post("id"), user_id(), "poolx"));

	$messages[] = "Removed Pool-x account.";

	// redirect to GET
	set_temporary_messages($messages);
	redirect(url_for('accounts_poolx'));
}

// get all of our accounts
$accounts = array();

$q = db()->prepare("SELECT * FROM accounts_poolx
	WHERE accounts_poolx.user_id=? ORDER BY title ASC");
$q->execute(array(user_id()));
$accounts = $q->fetchAll();

page_header("Your Accounts: BTC Addresses", "page_accounts_blockchain");

?>

<p>
<a href="<?php echo htmlspecialchars(url_for('accounts')); ?>">&lt; Back to Your Accounts</a>
</p>

<h1>Your Pool-x.eu Accounts</h1>

<table class="standard">
<thead>
	<tr>
		<th>Title</th>
		<th>Added</th>
		<th>Last checked</th>
		<th>Balance</th>
		<th></th>
	</tr>
</thead>
<tbody>
<?php foreach ($accounts as $a) {
	// an account may have multiple currency balances
	$q = db()->prepare("SELECT * FROM balances WHERE user_id=? AND account_id=? AND exchange=? AND is_recent=1 ORDER BY currency ASC");
	$q->execute(array(user_id(), $a['id'], 'poolx'));
	$balances = array();
	$last_updated = null;
	while ($balance = $q->fetch()) {
		$balances[$balance['currency']] = $balance['balance'];
		$last_updated = $balance['created_at'];
	}
?>
	<tr>
		<td><?php echo $a['title'] ? htmlspecialchars($a['title']) : "<i>untitled</i>"; ?></td>
		<td><?php echo recent_format_html($a['created_at']); ?></td>
		<td><?php echo recent_format_html($last_updated); ?></td>
		<td><ul><?php
			foreach ($balances as $c => $value) {
				echo "<li>" . currency_format($c, $value) . "</li>\n";
			}
		?></ul></td>
		<td>
			<form action="<?php echo htmlspecialchars(url_for('accounts_poolx')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
				<input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm('Are you sure you want to remove this account?');">
			</form>
		</td>
	</tr>
<?php } ?>
	<tr>
		<td colspan="5">
			<form action="<?php echo htmlspecialchars(url_for('accounts_poolx')); ?>" method="post">
				<table class="form">
				<tr>
					<th><label for="title">Title:</label></th>
					<td><input id="title" type="text" name="title" size="18" maxlength="64" value="<?php echo htmlspecialchars(require_post("title", "")); ?>"> (optional)</td>
				</tr>
				<tr>
					<th><label for="api_key">API Key:</label></th>
					<td><input id="api_key" type="text" name="api_key" size="48" maxlength="64" value="<?php echo htmlspecialchars(require_post("api_key", "")); ?>"></td>
				</tr>
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

<?php
page_footer();
