<?php

/**
 * Goes through a users' accounts and identifies which accounts might need automatic transaction
 * generation deleted.
 */

// create a map of all the current user exchanges
$accounts = account_data_grouped();
$current_accounts = array();
foreach ($accounts as $label => $accounts_data) {
	foreach ($accounts_data as $exchange => $account) {
		if (!isset($account['wizard']))
			continue;

		$wizard = get_wizard_account_type($account['wizard']);
		if (!$wizard['transaction_creation'])
			continue;

		$q = db()->prepare("SELECT * FROM " . $account['table'] . " WHERE user_id=?" . (isset($account['query']) ? $account['query'] : false));
		$q->execute(array($job['user_id']));
		while ($a = $q->fetch()) {
			$a['exchange'] = $exchange;
			$current_accounts[$exchange . " " . $a['id']] = $a;
		}
	}
}

crypto_log("User " . $job['user_id'] . " has " . count($current_accounts) . " accounts to parse.");

// are there any creators that need to be deleted for this user?
$q = db()->prepare("SELECT * FROM transaction_creators WHERE user_id=?");
$q->execute(array($job['user_id']));
$to_delete = array();
while ($a = $q->fetch()) {
	if (!isset($current_accounts[$a['exchange'] . " " . $a['account_id']])) {
		$to_delete[] = $a['id'];
	} else {
		unset($current_accounts[$a['exchange'] . " " . $a['account_id']]);
	}
}

if ($to_delete) {
	crypto_log("Need to delete " . count($to_delete) . " old transaction creators");

	$q = db()->prepare("DELETE FROM transaction_creators WHERE user_id=? AND id IN (" . implode(",", $to_delete) . ")");
	$q->execute(array($job['user_id']));
}

crypto_log("Complete.");
