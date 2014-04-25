<?php

require(__DIR__ . "/../inc/global.php");
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
			$account_data['disabled'] = $value['disabled'];
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

// process extra field inline edit
if (require_post("key", false) !== false && require_post("id", false)) {
	$id = require_post("id");
	$key = require_post("key");
	$value = require_post("value");
	$exchange = require_post("type");

	// check that this is a valid property to change for this wizard
	if (!isset($account_data['wizard'])) {
		throw new Exception("No wizard data found");
	}
	$wizard_type = get_wizard_account_type($account_data['wizard']);
	if (!isset($wizard_type['display_editable'][$key])) {
		throw new Exception("Key '" . htmlspecialchars($key) . "' is not a valid editable key");
	}

	// check that this is a valid input for this key
	$config = get_accounts_wizard_config($exchange);
	if (!isset($config['inputs'][$key])) {
		throw new Exception("A '" . htmlspecialchars($exchange) . "' does not have an input '" . htmlspecialchars($key) . "'");
	}

	if (isset($config['inputs'][$key]['number']) && $config['inputs'][$key]['number']) {
		// remove any commas
		$value = number_unformat($value);
	}
	$callback = $config['inputs'][$key]['callback'];

	if (!$callback($value)) {
		$errors[] = "'" . htmlspecialchars($value) . "' is not a valid " . htmlspecialchars($account_data['title']) . " " . htmlspecialchars($config['inputs'][$key]['title']) . ".";
	} else {
		$q = db()->prepare("UPDATE " . $account_data['table'] . " SET " . $config['inputs'][$key]['key'] . "=? WHERE user_id=? AND id=?");
		$q->execute(array($value, user_id(), $id));
		$messages[] = "Updated " . htmlspecialchars($account_data['title']) . " " . htmlspecialchars($config['inputs'][$key]['title']) . ".";

		// redirect to GET
		set_temporary_messages($messages);
		redirect(url_for(require_post("callback")));
	}

}

// process add
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
	if ($account_data['disabled']) {
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
		$title = htmlspecialchars(require_post("title", ""));

		// do we need to handle coinbase OAuth2?
		if ($account_data['exchange'] == "coinbase") {
			if (require_get("code", false)) {
				$query .= ", api_code=?";
				$args[] = require_get("code");
				$title = $_SESSION["coinbase_title"];
			} else {
				// need to get a code
				$_SESSION["coinbase_title"] = $title;		// we can't pass title to the redirect_uri, or we'll have to use this uri forever
				redirect(url_add("https://coinbase.com/oauth/authorize", array(
					"response_type" => "code",
					"client_id" => get_site_config('coinbase_client_id'),
					"redirect_uri" => absolute_url(url_for('coinbase')),
					"scope" => "balance",
				)));
			}
		}

		// we don't care if the address already exists
		$q = db()->prepare("INSERT INTO " . $account_data['table'] . " SET user_id=?, title=? $query");
		$full_args = array_join(array(user_id(), require_post("title", false)), $args);
		$q->execute($full_args);
		$id = db()->lastInsertId();
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

		// update has_added_account
		$q = db()->prepare("UPDATE users SET has_added_account=1,last_account_change=NOW() WHERE id=?");
		$q->execute(array(user_id()));

		// redirect to GET
		set_temporary_errors($errors);
		set_temporary_messages($messages);
		redirect(url_for(require_post("callback")));
	}
}

// process 'disable'
if (require_post("disable", false) && require_post("id", false)) {
	$q = db()->prepare("UPDATE " . $account_data['table'] . " SET is_disabled=1,is_disabled_manually=1 WHERE id=? AND user_id=?");
	$q->execute(array(require_post("id"), user_id()));

	$messages[] = "Disabled " . htmlspecialchars($account_data['title']) . ".";

	// redirect to GET
	set_temporary_errors($errors);
	set_temporary_messages($messages);
	redirect(url_for(require_post("callback")));
}

// process 'delete'
if (require_post("delete", false) && require_post("id", false)) {
	$q = db()->prepare("DELETE FROM " . $account_data['table'] . " WHERE id=? AND user_id=?");
	$q->execute(array(require_post("id"), user_id()));

	// also delete old address balances, since we won't be able to use them any more
	$q = db()->prepare("DELETE FROM balances WHERE account_id=? AND user_id=? AND exchange=?");
	$q->execute(array(require_post("id"), user_id(), $account_data['exchange']));

	// issue #201: also delete old hashrate data
	$q = db()->prepare("DELETE FROM hashrates WHERE account_id=? AND user_id=? AND exchange=?");
	$q->execute(array(require_post("id"), user_id(), $account_data['exchange']));

	// we also need to remove old _securities and _wallet balances for this exchange as well
	// fixes bug described by Tobias
	$q = db()->prepare("DELETE FROM balances WHERE account_id=? AND user_id=? AND exchange=?");
	$q->execute(array(require_post("id"), user_id(), $account_data['exchange'] . '_securities'));
	$q = db()->prepare("DELETE FROM balances WHERE account_id=? AND user_id=? AND exchange=?");
	$q->execute(array(require_post("id"), user_id(), $account_data['exchange'] . '_wallet'));

	// finally, mark old securities as no longer recent
	// (this will hide them from the Your Securities page as well)
	$q = db()->prepare("UPDATE securities SET is_recent=0 WHERE user_id=? AND exchange=? AND account_id=?");
	$q->execute(array(user_id(), $account_data['exchange'], require_post("id")));

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
	} else if ($account_data['disabled']) {
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
	if ($account_data['disabled']) {
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

