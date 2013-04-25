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
if (require_post("add", false) && require_post("address", false)) {
	if (!is_valid_btc_address(require_post("address"))) {
		$errors[] = "'" . htmlspecialchars(require_post("address")) . "' is not a valid BTC address.";
	} else {
		// we don't care if the address already exists
		$q = db()->prepare("INSERT INTO addresses SET user_id=?, address=?, currency=?");
		$q->execute(array(user_id(), require_post("address"), 'btc'));
		$messages[] = "Added new BTC address " . btc_address(require_post("address")) . ".";

		// redirect to GET
		set_temporary_messages($messages);
		redirect(url_for('accounts_blockchain'));
	}
}

if (require_post("delete", false) && require_post("id", false)) {
	$q = db()->prepare("DELETE FROM addresses WHERE id=? AND user_id=?");
	$q->execute(array(require_post("id"), user_id()));

	// also delete old address balances, since we won't be able to use them any more
	$q = db()->prepare("DELETE FROM address_balances WHERE address_id=? AND user_id=?");
	$q->execute(array(require_post("id"), user_id()));

	$messages[] = "Removed BTC address ID " . htmlspecialchars(require_post("id")) . ".";

	// redirect to GET
	set_temporary_messages($messages);
	redirect(url_for('accounts_blockchain'));
}

// get all of our accounts
$accounts = array();

$q = db()->prepare("SELECT
		addresses.id,
		addresses.address,
		addresses.created_at,
		addresses.user_id,
		address_balances.created_at AS last_updated,
		address_balances.balance
	FROM addresses
	LEFT JOIN (SELECT * FROM address_balances WHERE user_id=? AND is_recent=1) AS address_balances ON addresses.id=address_balances.address_id
	WHERE addresses.user_id=? AND addresses.currency=? ORDER BY address ASC");
$q->execute(array(user_id(), user_id(), 'btc'));
$accounts = $q->fetchAll();

page_header("Your Accounts: BTC Addresses", "page_accounts_blockchain");

?>

<p>
<a href="<?php echo htmlspecialchars(url_for('accounts')); ?>">&lt; Back to Your Accounts</a>
</p>

<h1>Your BTC Addresses</h1>

<table class="standard">
<thead>
	<tr>
		<th>Address</th>
		<th>Added</th>
		<th>Last checked</th>
		<th>Balance</th>
		<th></th>
	</tr>
</thead>
<tbody>
<?php foreach ($accounts as $a) { ?>
	<tr>
		<td><?php echo btc_address($a['address']); ?></td>
		<td><?php echo recent_format_html($a['created_at']); ?></td>
		<td><?php echo recent_format_html($a['last_updated']); ?></td>
		<td><?php echo $a['balance'] === null ? "-" : currency_format('btc', $a['balance']); ?></td>
		<td>
			<form action="<?php echo htmlspecialchars(url_for('accounts_blockchain')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
				<input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm('Are you sure you want to remove this address?');">
			</form>
		</td>
	</tr>
<?php } ?>
	<tr>
		<td colspan="5">
			<form action="<?php echo htmlspecialchars(url_for('accounts_blockchain')); ?>" method="post">
				<label>BTC address: <input type="text" name="address" size="36" maxlength="36" value="<?php echo htmlspecialchars(require_post("address", "")); ?>"></li>
				<input type="submit" name="add" value="Add address" class="add">
			</form>
		</td>
	</tr>
</tbody>
</table>

<?php
page_footer();
