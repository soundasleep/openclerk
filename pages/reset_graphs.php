<?php

require(__DIR__ . "/../layout/graphs.php");
require_login();

$messages = array();
$errors = array();
if (require_post("confirm", false)) {
  reset_user_graphs(user_id());

  $messages[] = t("User graphs and pages successfully reset.");
} else {
  $errors[] = t("Did not reset user graphs and pages: you need to select the confirmation checkbox.");
}

set_temporary_messages($messages);
set_temporary_errors($errors);
redirect(url_for('profile'));
