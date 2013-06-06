<?php

require("inc/global.php");
require("layout/graphs.php");
require_login();

require("layout/templates.php");	// for currency_format

// adding offset
$currencies = get_all_currencies();
$messages = array();
$errors = array();
foreach ($currencies as $c) {
	if (require_post($c, false) !== false) {
		if (!is_numeric(require_post($c))) {
			$errors[] = "'" . htmlspecialchars(require_post($c)) . "' is not a valid numeric value for " . htmlspecialchars(strtoupper($c)) . ".";
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
			"balance" => require_post($c),
		));

		// we shouldn't have to wait for an offset value to be recalculated with a summary so that it's displayed correctly;
		// we will just update the most recent summary_instance for this currency to the correct balance (based on the previous
		// offset value)
		$q = db()->prepare("UPDATE summary_instances SET balance=balance+:offset WHERE user_id=:user_id AND summary_type=:summary_type AND is_recent=1");
		$q->execute(array(
			"offset" => require_post($c) - $offset,
			"summary_type" => "total".$c,
			"user_id" => user_id(),
		));

		$messages[] = "Set " . htmlspecialchars(strtoupper($c)) . " offset to " . currency_format($c, require_post($c), 8) . ".";
	}
}

set_temporary_messages($messages);
set_temporary_errors($errors);
redirect(url_for('profile', array('page' => require_get('page', false))));
