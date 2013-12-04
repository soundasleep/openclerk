<?php

require(__DIR__ . "/inc/global.php");
require_login();

$user = get_user(user_id());
require_user($user);

$errors = array();
$messages = array();

// get all of our accounts
$accounts = user_limits_summary(user_id());

// find the appropriate $account_data
$account_data = false;
foreach (account_data_grouped() as $label => $data) {
	foreach ($data as $key => $value) {
		if ($key == require_post("type")) {
			// we've found a valid account type
			$account_data = get_accounts_wizard_config($key);
			if (isset($value['disabled'])) {
				$account_data['disabled'] = $value['disabled'];
			}
		}
	}
}

if (!$account_data) {
	throw new Exception("Invalid account type '" . htmlspecialchars(require_post("type")) . "'");
}

switch (require_post("callback")) {
	case "wizard_accounts_pools":
	case "wizard_accounts_exchanges":
	case "wizard_accounts_securities":
	case "wizard_accounts_individual_securities":
	case "wizard_accounts_other":
		break;

	default:
		throw new Exception("Invalid callback '" . htmlspecialchars(require_post("callback")) . "'");
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
		redirect(url_for(require_post("callback")));
	}

}

// process add/delete
if (require_post("add", false)) {
	$query = "";
	$args = array();
	foreach ($account_data['inputs'] as $key => $data) {
		$callback = $data['callback'];
		if (!$callback(require_post($key))) {
			$errors[] = "That is not a valid " . htmlspecialchars($account_data['title']) . " " . $data['title'] . ".";
			break;
		} else {
			$query .= ", $key=?";
			$args[] = require_post($key);
		}
	}
	if (isset($account_data['disabled']) && $account_data['disabled']) {
		$errors[] = "Cannot add a new account; that account type is disabled.";
	}
	if (!is_valid_title(require_post("title", false))) {
		$errors[] = "That is not a valid title.";
	}
	if (!can_user_add($user, $account_data['exchange'])) {
		$errors[] = "Cannot add " . $account_data['title'] . ": too many existing accounts.<br>" .
				($user['is_premium'] ? "" : " To add more " . $account_data['titles'] . ", upgrade to a <a href=\"" . htmlspecialchars(url_for('premium')) . "\">premium account</a>.");
	}
	if (!$errors) {
		// we don't care if the address already exists
		$q = db()->prepare("INSERT INTO " . $account_data['table'] . " SET user_id=?, title=? $query");
		$full_args = array_join(array(user_id(), require_post("title", false)), $args);
		$q->execute($full_args);
		$id = db()->lastInsertId();
		$title = htmlspecialchars(require_post("title", ""));
		if (!$title) $title = "<i>(untitled)</i>";
		$messages[] = "Added new " . htmlspecialchars($account_data['title']) . " <i>" . $title . "</i>. Balances from this account will be retrieved shortly.";

		// create a test job for this new account
		$q = db()->prepare("INSERT INTO jobs SET
					job_type=:job_type,
					user_id=:user_id,
					arg_id=:arg_id,
					priority=:priority,
					is_test_job=1");
		$q->execute(array('job_type' => $account_data['exchange'], 'user_id' => user_id(), 'arg_id' => $id, 'priority' => get_site_config('job_test_priority')));

		// redirect to GET
		set_temporary_errors($errors);
		set_temporary_messages($messages);
		redirect(url_for(require_post("callback")));
	}
}

if (require_post("delete", false) && require_post("id", false)) {
	$q = db()->prepare("DELETE FROM " . $account_data['table'] . " WHERE id=? AND user_id=?");
	$q->execute(array(require_post("id"), user_id()));

	// also delete old address balances, since we won't be able to use them any more
	$q = db()->prepare("DELETE FROM balances WHERE account_id=? AND user_id=? AND exchange=?");
	$q->execute(array(require_post("id"), user_id(), $account_data['exchange']));

	// we also need to remove old _securities and _wallet balances for this exchange as well
	// fixes bug described by Tobias
	$q = db()->prepare("DELETE FROM balances WHERE account_id=? AND user_id=? AND exchange=?");
	$q->execute(array(require_post("id"), user_id(), $account_data['exchange'] . '_securities'));
	$q = db()->prepare("DELETE FROM balances WHERE account_id=? AND user_id=? AND exchange=?");
	$q->execute(array(require_post("id"), user_id(), $account_data['exchange'] . '_wallet'));

	$messages[] = "Removed " . htmlspecialchars($account_data['title']) . ".";

	// redirect to GET
	set_temporary_errors($errors);
	set_temporary_messages($messages);
	redirect(url_for(require_post("callback")));
}

// process 'test'
if (require_post('test', false) && require_post('id', false)) {
	// do we already have a job queued up?
	$q = db()->prepare("SELECT * FROM jobs WHERE is_executed=0 AND user_id=? AND is_test_job=1 LIMIT 1");
	$q->execute(array(user_id()));

	if ($job = $q->fetch()) {
		$errors[] = "Cannot create a " . htmlspecialchars($account_data['title']) . " test, because you already have a " . get_exchange_name($job['job_type']) . " test pending.";
	} else if (isset($account_data['disabled']) && $account_data['disabled']) {
		$errors[] = "Cannot test that job; that account type is disabled.";
	} else {
		$q = db()->prepare("INSERT INTO jobs SET
			job_type=:job_type,
			user_id=:user_id,
			arg_id=:arg_id,
			priority=:priority,
			is_test_job=1");
		$q->execute(array('job_type' => $account_data['exchange'], 'user_id' => user_id(), 'arg_id' => require_post('id'), 'priority' => get_site_config('job_test_priority')));

		$messages[] = "Queued up " . htmlspecialchars($account_data['title']) . " test; results should be available shortly.";

		set_temporary_messages($messages);
		redirect(url_for(require_post("callback")));

	}

}

// process 'enable'
if (require_post('enable', false) && require_post('id', false)) {
	if (isset($account_data['disabled']) && $account_data['disabled']) {
		$errors[] = "Cannot enable that account; that account type is disabled.";
	} else {
		// reset all failure fields
		$q = db()->prepare("UPDATE " . $account_data['table'] . " SET is_disabled=0,first_failure=NULL,failures=0 WHERE id=? AND user_id=?");
		$q->execute(array(require_post("id"), user_id()));

		$messages[] = "Enabled " . htmlspecialchars($account_data['title']) . ".";

		set_temporary_messages($messages);
		redirect(url_for(require_post("callback")));
	}

}

// either there was an error or we haven't done anything; go back to callback
set_temporary_errors($errors);
set_temporary_messages($messages);
$_SESSION['wizard_data'] = $_POST;		// store so we can restore it on the callback page
redirect(url_for(require_post("callback"), array("title" => require_post("title", false), "exchange" => require_post("type", false))));

