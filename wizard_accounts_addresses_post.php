<?php

require(__DIR__ . "/inc/global.php");
require_login();

require(__DIR__ . "/layout/templates.php");	// for btc_address() etc

$user = get_user(user_id());
require_user($user);

$messages = array();
$errors = array();

$currency = require_post("currency");
$account_data = get_blockchain_wizard_config($currency);

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
		redirect(url_for("wizard_accounts_addresses#wizard_" . $currency));
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
		redirect(url_for("wizard_accounts_addresses#wizard_" . $currency));
	}
}

// process delete
if (require_post("delete", false) && require_post("id", false)) {
	// find the original address so we can display it
	$q = db()->prepare("SELECT * FROM " . $account_data['table'] . " WHERE id=? AND user_id=?");
	$q->execute(array(require_post("id"), user_id()));
	$address = $q->fetch();

	$q = db()->prepare("DELETE FROM " . $account_data['table'] . " WHERE id=? AND user_id=?");
	$q->execute(array(require_post("id"), user_id()));

	// also delete old address balances, since we won't be able to use them any more
	$q = db()->prepare("DELETE FROM address_balances WHERE address_id=? AND user_id=?");
	$q->execute(array(require_post("id"), user_id()));

	$address_callback = $account_data['address_callback'];
	$messages[] = "Removed " . htmlspecialchars($account_data['title']) . " " . ($address ? $address_callback($address['address']) : " (removed)") . ".";

	// redirect to GET
	set_temporary_messages($messages);
	redirect(url_for("wizard_accounts_addresses#wizard_" . $currency));
}

/**
 * @param $row may be array(address) or array(title, address)
 */
function process_csv_upload_row($row) {
	global $messages;
	global $errors;
	global $addresses, $account_data, $user;

	global $invalid_addresses, $updated_titles, $existing_addresses, $new_addresses, $limited_addresses;

	if (count($row) >= 2) {
		$title = trim($row[0]);
		$address = trim($row[1]);
	} else {
		$title = false;
		$address = trim($row[0]);
	}
	if ($address == 'Address') {
		// skip the first header line of CSV file, if present
		return;
	}
	if (!trim($address)) {
		// ignore empty addresses
		return;
	}

	// otherwise, row[0] should be a label, and row[1] should be an address
	if (!$account_data['callback']($address)) {
		$invalid_addresses++;
	} else {
		// do we already have this address?
		if (isset($addresses[$address])) {
			$existing_addresses++;
			// do we need to update the title?
			if ($title !== false && $addresses[$address]['title'] != $title) {
				$q = db()->prepare("UPDATE " . $account_data['table'] . " SET title=? WHERE user_id=? AND id=?");
				$q->execute(array($row[0], user_id(), $addresses[$address]['id']));
				$addresses[$address]['title'] = $title;
				$updated_titles++;
			}
		} else {
			// we need to insert in a new address
			if (!can_user_add($user, $account_data['premium_group'], $new_addresses + 1)) {
				$limited_addresses++;

			} else {
				if ($title) {
					$q = db()->prepare("INSERT INTO " . $account_data['table'] . " SET user_id=?, address=?, currency=?, title=?");
					$q->execute(array(user_id(), $address, $account_data['currency'], $title));
				} else {
					$q = db()->prepare("INSERT INTO " . $account_data['table'] . " SET user_id=?, address=?, currency=?");
					$q->execute(array(user_id(), $address, $account_data['currency']));
				}
				$addresses[$address] = array('id' => db()->lastInsertId(), 'title' => false);
				$new_addresses++;
			}
		}
	}
}

// process file upload
if (isset($_FILES['csv']) || require_post('addresses', false)) {
	if (isset($_FILES['csv'])) {
		try {
			// throws a BlockedException if this IP has requested this too many times recently
			check_heavy_request();
		} catch (BlockedException $e) {
			$errors[] = $e->getMessage();
			set_temporary_errors($errors);
			redirect(url_for("wizard_accounts_addresses#wizard_" . $currency));
		}
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
	if (isset($_FILES['csv'])) {
		$fp = fopen($_FILES['csv']['tmp_name'], "r");
		while ($fp && ($row = fgetcsv($fp, 1000, ",")) !== false) {
			process_csv_upload_row($row);
		}
	} else {
		// TODO using explode() here is not great; should use CSV functions instead (maybe fopen on a string?)
		$input = explode("\n", require_post("addresses"));
		foreach ($input as $row) {
			if (require_post("title", false)) {
				$row = require_post("title") . "," . $row;
			}
			process_csv_upload_row(explode(",", $row));
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
		updated " . plural($existing_addresses, "existing address", "existing addresses") . ".";

	// redirect to GET
	set_temporary_messages($messages);
	set_temporary_errors($errors);
	redirect(url_for("wizard_accounts_addresses#wizard_" . $currency));
}

// otherwise we've got some other error; continue to redirect to GET
set_temporary_messages($messages);
set_temporary_errors($errors);
redirect(url_for("wizard_accounts_addresses#wizard_" . $currency));
