<?php

require("inc/global.php");
require("layout/graphs.php");
require_login();

require("layout/templates.php");	// for currency_format

// adding offset
$currencies = get_all_currencies();
$messages = array();
foreach ($currencies as $c) {
	if (require_post($c, false) !== false) {
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

		$messages[] = "Set " . htmlspecialchars(strtoupper($c)) . " offset to " . currency_format($c, require_post($c), 8) . ".";
	}
}

set_temporary_messages($messages);
redirect(url_for('profile', array('page_id' => require_get('page_id', false))));
