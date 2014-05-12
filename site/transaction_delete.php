<?php

// delete a (manual or automatic) transaction

require(__DIR__ . "/../inc/global.php");
require_login();

$id = (int) require_post("id");

$messages = array();
$errors = array();

$q = db()->prepare("DELETE FROM transactions WHERE user_id=? AND id=?");
$q->execute(array(user_id(), $id));

$messages[] = t("Deleted transaction.");

set_temporary_messages($messages);
set_temporary_errors($errors);

redirect(url_for('your_transactions'));
