<?php

require(__DIR__ . "/inc/global.php");
require(__DIR__ . "/layout/graphs.php");
require_login();

require(__DIR__ . "/layout/templates.php");	// for currency_format

// adding offset
$currencies = get_all_currencies();
$messages = array();
$errors = array();
foreach ($currencies as $c) {
	$value = require_post($c, false);
	if (require_post("wizard", false)) {
		$value = require_post($c, 0);
		if (trim($value) == "") {
			$value = 0;
		}
	}

	if ($value !== false) {
		// remove any commas
		$value = number_unformat($value);
		if (!is_numeric($value)) {
			$errors[] = "'" . htmlspecialchars($value) . "' is not a valid numeric value for " . htmlspecialchars(get_currency_abbr($c)) . ".";
			continue;
		}

		// get old offset value
		$q = db()->prepare("SELECT * FROM offsets WHERE is_recent=1 AND currency=:currency AND user_id=:user_id LIMIT 1");
		$q->execute(array(
			"currency" => $c,
			"user_id" => user_id(),
		));
		$offset = $q->fetch();
		$offset = $offset ? $offset['balance'] : 0;

		// update old recent values
		$q = db()->prepare("UPDATE offsets SET is_recent=0 WHERE currency=:currency AND user_id=:user_id");
		$q->execute(array(
			"currency" => $c,
			"user_id" => user_id(),
		));

		// insert in new ticker value
		$q = db()->prepare("INSERT INTO offsets SET is_recent=1, currency=:currency, user_id=:user_id, balance=:balance");
		$q->execute(array(
			"currency" => $c,
			"user_id" => user_id(),
			"balance" => $value,
		));

		// we shouldn't have to wait for an offset value to be recalculated with a summary so that it's displayed correctly;
		// we will just update the most recent summary_instance for this currency to the correct balance (based on the previous
		// offset value)
		$q = db()->prepare("UPDATE summary_instances SET balance=balance+:offset WHERE user_id=:user_id AND summary_type=:summary_type AND is_recent=1");
		$q->execute(array(
			"offset" => $value - $offset,
			"summary_type" => "total".$c,
			"user_id" => user_id(),
		));

		if (!require_post("wizard", false)) {
			$messages[] = "Set " . htmlspecialchars(get_currency_abbr($c)) . " offset to " . currency_format($c, $value, 8) . ".";
		}
	}
}

if (require_post("wizard", false)) {
	$messages[] = "Updated currency offsets.";
}

set_temporary_messages($messages);
set_temporary_errors($errors);
if (require_post("wizard", false)) {
	redirect(url_for('wizard_accounts'));
} else {
	redirect(url_for('profile', array('page' => require_get('page', false))));
}

