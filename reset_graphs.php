<?php

require("inc/global.php");
require("layout/graphs.php");
require_login();

$user = get_user(user_id());
if (!$user) {
	throw new Exception("Could not find that user.");
}

$messages = array();
$errors = array();
if (require_post("confirm", false)) {
	reset_user_graphs(user_id());

	$messages[] = "User graphs and pages successfully reset.";
} else {
	$errors[] = "Did not reset user graphs and pages: you need to select the confirmation checkbox.";
}

set_temporary_messages($messages);
set_temporary_errors($errors);
redirect(url_for('profile'));
