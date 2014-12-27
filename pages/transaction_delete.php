<?php

// delete a (manual or automatic) transaction

require_login();

$id = (int) require_post("id");
$page_args = require_post("page_args", false);

$messages = array();
$errors = array();

$q = db()->prepare("DELETE FROM transactions WHERE user_id=? AND id=?");
$q->execute(array(user_id(), $id));

$messages[] = t("Deleted transaction.");

set_temporary_messages($messages);
set_temporary_errors($errors);

$args = array();
if (is_array($page_args)) {
	foreach ($page_args as $key => $value) {
		$args[$key] = $value;
	}
}

redirect(url_for('your_transactions', $args));
