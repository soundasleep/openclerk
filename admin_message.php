<?php

/**
 * Admin page: read/hide message
 */

require(__DIR__ . "/inc/global.php");
require_admin();

$id = (int) require_get("id");

$q = db()->prepare("UPDATE admin_messages SET is_read=1 WHERE id=?");
$q->execute(array($id));

$messages[] = "Hid admin message $id.";
set_temporary_messages($messages);
redirect(url_for('index'));
