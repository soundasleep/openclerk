<?php

require("inc/global.php");
require("layout/graphs.php");
require_login();

$user = get_user(user_id());
require_user($user);

$added = array();
$messages = array();
$errors = array();

// first we delete things, then we add things, so that we can swap currencies without hitting limits

// go through all summary types (delete)
foreach (get_summary_types() as $key => $data) {
	if (!require_post($key, false)) {
		// unless there's not already one here
		// (this is mostly just to produce the correct message)
		$q = db()->prepare("SELECT * FROM summaries WHERE user_id=? AND summary_type=? LIMIT 1");
		$q->execute(array(user_id(), $key));

		if ($q->fetch()) {
			$messages[] = "Removed currency " . $data['title'] . ".";
		}

		// no point in keeping old data: delete it
		$q = db()->prepare("DELETE FROM summary_instances WHERE user_id=? AND summary_type=?");
		$q->execute(array(user_id(), $key));

		$q = db()->prepare("DELETE FROM summaries WHERE user_id=? AND summary_type=?");
		$q->execute(array(user_id(), $key));
	}
}

// go through all summary types (add)
foreach (get_summary_types() as $key => $data) {
	if (require_post($key, false)) {
		// unless there's already one here
		$q = db()->prepare("SELECT * FROM summaries WHERE user_id=? AND summary_type=? LIMIT 1");
		$q->execute(array(user_id(), $key));
		if (!$q->fetch()) {
			// check premium account status, need to recheck after every add
			$q = db()->prepare("SELECT COUNT(*) AS c FROM summaries WHERE user_id=?");
			$q->execute(array(user_id()));
			$count = $q->fetch()["c"];

			if ($count >= get_premium_config('summaries_' . ($user['is_premium'] ? 'premium' : 'free'))) {
				// too many
				$errors[] = "Could not add currency " . $data['title'] . ": too many currencies defined." .
						($user['is_premium'] ? "" : " To add more currencies, upgrade to a <a href=\"" . htmlspecialchars(url_for('premium')) . "\">premium account</a>.");

			} else {
				// OK to add
				$q = db()->prepare("INSERT INTO summaries SET user_id=?, summary_type=?");
				$q->execute(array(user_id(), $key));

				$added[] = $data['title'];
			}
		}
	}
}


if ($added) {
	$messages[] = "Added new " . (count($added) == 1 ? "currency" : "currencies") . ": " . implode(", ", $added) . ". The first results for these summaries will shortly be compiled by the system.";
}
$messages[] = "Currency settings updated.";
set_temporary_messages($messages);
set_temporary_errors($errors);
redirect(url_for('user'));
