<?php

/**
 * Process selected currencies and redirect to the next wizard page if successful.
 */

require(__DIR__ . "/inc/global.php");
require_login();

$user = get_user(user_id());
require_user($user);

$errors = array();
$messages = array();

require(__DIR__ . "/graphs/managed.php");

// get all of our limits
$accounts = user_limits_summary(user_id());

$preferred_crypto = require_post("preferred_crypto", false);
$preferred_fiat = require_post("preferred_fiat", false);
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
if (!is_fiat_currency($preferred_fiat)) {
	$errors[] = "Invalid preferred fiat currency.";
}
if (!in_array($preference, array('auto', 'managed', 'none'))) {
	$errors[] = "Invalid graph management preference.";
}
if ($preference != "none" && !$preferred_fiat) {
	$errors[] = "You need to select at least <a href=\"" . htmlspecialchars(url_for('wizard_currencies')) . "\">one fiat currency</a> in order to use managed graphs.";
}
if ($preference != "none" && !$preferred_crypto) {
	$errors[] = "You need to select at least <a href=\"" . htmlspecialchars(url_for('wizard_currencies')) . "\">one fiat currency</a> in order to use managed graphs.";
}
foreach ($managed as $m) {
	if (!isset($categories[$m])) {
		$errors[] = "'" . htmlspecialchars($m) . "' is not a valid graph portfolio preference.";
	}
}

// check that this user can have this many graphs
if ($preference != 'none') {
	$generated_graphs = calculate_user_graphs($user, $preference, $managed);

	// if 'managed', also merge in any graphs that are already present on the final page
	// ('auto' will reset the page anyway)
	if ($preference == 'managed') {
		$q = db()->prepare("SELECT * FROM graph_pages WHERE is_managed=1 AND user_id=?");
		$q->execute(array(user_id()));
		if ($graph_page = $q->fetch()) {
			$q = db()->prepare("SELECT * FROM graphs WHERE page_id=?");
			$q->execute(array($graph_page['id']));
			while ($graph = $q->fetch()) {
				// only add it if it's not managed, because otherwise we can remove it
				if (!$graph['is_managed']) {
					$generated_graphs[$graph['graph_type']] = $graph;
				}
			}
		}
	}

	if (count($generated_graphs) > get_premium_value($user, 'graphs_per_page')) {
		$errors[] = "Cannot update report preferences: this would add too many graphs to the managed graph page." .
				($user['is_premium'] ? "" : " To add more graphs on the managed graph page, upgrade to a <a href=\"" . htmlspecialchars(url_for('premium')) . "\">premium account</a>.");
		if (is_admin()) {
			$errors[] = "(admin) " . print_r(array_keys($generated_graphs), true) . " (" . count($generated_graphs) . " > " . get_premium_value($user, 'graphs_per_page') . ")";
		}
	}

}

// check that this user can have this many graph pages
if ($preference != 'none') {
	// we will be inserting in a new page possibly, so +1
	$q = db()->prepare("SELECT COUNT(*) AS c FROM graph_pages WHERE is_managed=0 AND user_id=?");
	$q->execute(array(user_id()));
	$count = $q->fetch();
	if (($count['c'] + 1) >= get_premium_value($user, 'graph_pages')) {
		// unless we will be resetting any old pages anyway
		if (get_premium_value($user, 'graph_pages') > 1) {
			$errors[] = "Cannot update report preferences: this would add too many graph pages." .
					($user['is_premium'] ? "" : " To add more graph pages, upgrade to a <a href=\"" . htmlspecialchars(url_for('premium')) . "\">premium account</a>.");
		}
	}
}

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
			// delete all managed pages, the next page load will display them
			// if the user can't have more than one page, then automatically
			// delete and reset the only available page
			$query_extra = (get_premium_value($user, 'graph_pages') > 1) ? " AND is_managed=1" : "";

			$q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=? $query_extra");
			$q->execute(array(user_id()));
			$pages = $q->fetchAll();

			foreach ($pages as $page) {
				$q = db()->prepare("DELETE FROM graphs WHERE page_id=?");
				$q->execute(array($page['id']));
			}

			$q = db()->prepare("DELETE FROM graph_pages WHERE user_id=? $query_extra");
			$q->execute(array(user_id()));

			$messages[] = "Reset graphs.";
		} else if ($update_needed) {
			// $messages[] = "Updated graphs.";
		}
	}
}

set_temporary_messages($messages);
set_temporary_errors($errors);

if ($errors) {
	redirect(url_for('wizard_reports', array('preference' => $preference)));	// go back
} else {
	redirect(url_for('profile'));	// go forward
}

