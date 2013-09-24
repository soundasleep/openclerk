<?php

/**
 * Process selected currencies and redirect to the next wizard page if successful.
 */

require("inc/global.php");
require_login();

$user = get_user(user_id());
require_user($user);

$errors = array();
$messages = array();

require("graphs/managed.php");

// get all of our accounts
$accounts = user_limits_summary(user_id());

$preferred_crypto = require_post("preferred_crypto");
$preferred_fiat = require_post("preferred_fiat");
$preference = require_post("preference");
$managed = require_post("managed", array());

$categories = get_managed_graph_categories();

// checks
if ($preference == "managed" && !$managed) {
	$errors[] = "You need to select at least one category of graph portfolio preferences.";
}
if (!in_array($preferred_crypto, get_all_cryptocurrencies())) {
	$errors[] = "Invalid preferred cryptocurrency.";
}
if (!in_array($preferred_fiat, get_all_fiat_currencies())) {
	$errors[] = "Invalid preferred fiat currency.";
}
if (!in_array($preference, array('auto', 'managed', 'none'))) {
	$errors[] = "Invalid graph management preference.";
}
foreach ($managed as $m) {
	if (!isset($categories[$m])) {
		$errors[] = "'" . htmlspecialchars($m) . "' is not a valid graph portfolio preference.";
	}
}

// TODO check that this user can have this many graphs

// do we need to update existing graphs? this will delete graphs that shouldn't exist,
// and add new ones, but leave existing ones alone (e.g. layout)
$update_needed = false;
// or do we need to totally delete everything?
$total_reset_needed = false;

// save
if (!$errors) {
	// save preferences
	$q = db()->prepare("UPDATE users SET preferred_crypto=:crypto, preferred_fiat=:fiat, graph_managed_type=:type WHERE id=:id");
	$q->execute(array(
		'crypto' => $preferred_crypto,
		'fiat' => $preferred_fiat,
		'type' => $preference,
		'id' => user_id(),
	));

	if ($user['preferred_crypto'] != $preferred_crypto || $user['preferred_fiat'] != $preferred_fiat) {
		$update_needed = true;
		$messages[] = "Updated preferred currency preferences.";
	}

	if ($user['graph_managed_type'] != $preference) {
		$messages[] = "Updated graph report management preference.";
	}

	// save managed preferences
	if ($preference == 'managed') {
		$existing_managed = array();
		$q = db()->prepare("SELECT * FROM managed_graphs WHERE user_id=?");
		$q->execute(array(user_id()));
		while ($m = $q->fetch()) {
			$existing_managed[] = $m['preference'];
		}

		// are they different?
		$different = false;
		if (count($managed) != count($existing_managed)) {
			$different = true;
		} else {
			$copy = $existing_managed;
			foreach ($managed as $m) {
				if (($key = array_search($m, $copy)) === FALSE) {
					$different = true;
				} else {
					unset($copy[$key]);
				}
			}
			if ($copy) {
				$different = true;
			}
		}

		if ($different) {
			$q = db()->prepare("DELETE FROM managed_graphs WHERE user_id=?");
			$q->execute(array(user_id()));

			foreach ($managed as $key) {
				$q = db()->prepare("INSERT INTO managed_graphs SET user_id=?, preference=?");
				$q->execute(array(user_id(), $key));
			}

			$messages[] = "Updated graph portfolio preferences.";

			$update_needed = true;
		}
	}

	if ($preference == 'none') {
		// disable all managed graphs, we'll assume they've all been added manually
		$q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=?");
		$q->execute(array(user_id()));
		$pages = $q->fetchAll();

		foreach ($pages as $page) {
			$q = db()->prepare("UPDATE graphs SET is_managed=0 WHERE page_id=?");
			$q->execute(array($page['id']));
		}

		$q = db()->prepare("UPDATE graph_pages SET is_managed=0 WHERE user_id=?");
		$q->execute(array(user_id()));
	}

	if ($user['graph_managed_type'] != 'auto' && $preference == 'auto') {
		// we are switching to auto; delete everything
		$total_reset_needed = true;
	} else if ($user['graph_managed_type'] == 'auto' && $preference == 'managed') {
		// we are switching from auto to managed; reset
		$total_reset_needed = true;
	}

	// update graphs or reset graphs if necessary
	if ($preference != "none") {
		// if we're submitting this form, and we are using some form of management, we should update the graphs anyway
		$update_needed = true;

		if ($update_needed || $total_reset_needed) {
			// we let the next page load handle updating graphs, so we can also
			// update graphs without forcing users to use the wizard
			$q = db()->prepare("UPDATE users SET needs_managed_update=1 WHERE id=?");
			$q->execute(array(user_id()));
		}

		if ($total_reset_needed) {
			// just delete everything, the next page load will display them
			$q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=?");
			$q->execute(array(user_id()));
			$pages = $q->fetchAll();

			foreach ($pages as $page) {
				$q = db()->prepare("DELETE FROM graph_pages WHERE id=?");
				$q->execute(array($page['id']));

				$q = db()->prepare("DELETE FROM graphs WHERE page_id=?");
				$q->execute(array($page['id']));
			}

			$messages[] = "Reset graphs.";
		} else if ($update_needed) {
			$messages[] = "Updated graphs.";
		}
	}
}

// TODO add parameters so that previous settings are not lost on error page redirect
set_temporary_messages($messages);
set_temporary_errors($errors);

if ($errors) {
	redirect(url_for('wizard_reports', array('preference' => $preference)));	// go back
} else {
	redirect(url_for('profile'));	// go forward
}

