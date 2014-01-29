<?php

/**
 * Admin post callback for deleting users from the system.
 */

require(__DIR__ . "/inc/global.php");
require_admin();

require(__DIR__ . "/layout/templates.php");
require(__DIR__ . "/layout/graphs.php");

$messages = array();
$errors = array();

page_header("Admin: Delete User", "page_admin_user_delete");
$id = require_post("id");
$confirm = require_post("confirm");
if (!$confirm) {
	throw new Exception("Need to confirm");
}

?>

<h1>Delete User</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin_user_list')); ?>">&lt; Back to User List</a></p>

<ul>
<?php
	$user = get_user($id);
	echo "<li>Deleting user " . ($user ? htmlspecialchars(print_r($user, true)) : "<i>(phantom)</i>") . "</li>\n";

	function delete_from($table) {
		global $id;
		// delete graph pages
		echo "<li>Deleting $table...";
		$q = db()->prepare("DELETE FROM $table WHERE user_id=?");
		$q->execute(array($id));
		echo " (" . number_format($q->rowCount()) . " rows deleted)</li>\n";
	}

	delete_from('valid_user_keys');

	// go through all accounts
	$already_done = array();
	foreach (account_data_grouped() as $label => $accounts) {
		foreach ($accounts as $key => $account) {
			if ($account['table'] != 'graphs' && !isset($already_done[$account['table']])) {
				delete_from($account['table']);
				$already_done[$account['table']] = 1;
			}
		}
	}

	delete_from('balances');
	delete_from('address_balances');
	delete_from('hashrates');
	delete_from('securities');

	delete_from('offsets');

	delete_from('openid_identities');

	delete_from('summary_instances');
	delete_from('summaries');

	delete_from('graph_data_summary');
	delete_from('graph_data_balances');

	delete_from('pending_subscriptions');

	// delete graphs
	echo "<li>Deleting graphs...";
	$q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=?");
	$q->execute(array($user['id']));
	$pages = $q->fetchAll();
	foreach ($pages as $page) {
		$q = db()->prepare("DELETE FROM graphs WHERE page_id=?");
		$q->execute(array($page['id']));
		echo " (" . number_format($q->rowCount()) . " rows deleted)";
	}
	echo "</li>\n";

	delete_from('graph_pages');
	delete_from('managed_graphs');

	// finally delete the user object
	echo "<li>Deleting user...";
	$q = db()->prepare("DELETE FROM users WHERE id=?");
	$q->execute(array($user['id']));
	echo " (" . number_format($q->rowCount()) . " rows deleted)</li>\n";

?>
</ul>

<?php
page_footer();
