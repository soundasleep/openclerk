<?php

function delete_from($table) {
	global $id;
	// delete graph pages
	crypto_log("Deleting $table...");
	$q = db()->prepare("DELETE FROM $table WHERE user_id=?");
	$q->execute(array($id));
	echo " (" . number_format($q->rowCount()) . " rows deleted)</li>\n";
}

function delete_user($id) {

	$user = get_user($id);
	if (!$user) {
		throw new Exception("No such user $id");
	}
	crypto_log("Deleting user " . ($user ? htmlspecialchars(print_r($user, true)) : "<i>(phantom)</i>"));

	delete_from('valid_user_keys');

	// go through all accounts
	$already_done = array();
	foreach (account_data_grouped() as $label => $accounts) {
		foreach ($accounts as $key => $account) {
			// don't try to export unsafe exchanges
			if ($account['unsafe'] && !get_site_config('allow_unsafe')) {
				continue;
			}

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
	crypto_log("Deleting graphs...");
	$q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=?");
	$q->execute(array($user['id']));
	$pages = $q->fetchAll();
	foreach ($pages as $page) {
		$q = db()->prepare("DELETE FROM graphs WHERE page_id=?");
		$q->execute(array($page['id']));
		crypto_log("(" . number_format($q->rowCount()) . " rows deleted)");
	}

	delete_from('graph_pages');
	delete_from('managed_graphs');

	// finally delete the user object
	crypto_log("Deleting user...");
	$q = db()->prepare("DELETE FROM users WHERE id=?");
	$q->execute(array($user['id']));
	crypto_log("(" . number_format($q->rowCount()) . " rows deleted)");

}
