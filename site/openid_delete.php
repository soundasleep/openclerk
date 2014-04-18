<?php

/**
 * Allows users to delete OpenID locations from their account.
 */

require(__DIR__ . "/../inc/global.php");
require_login();

$messages = array();
$errors = array();

// make sure we aren't deleting our last identity
$q = db()->prepare("SELECT COUNT(*) AS c FROM openid_identities WHERE user_id=?");
$q->execute(array(user_id()));
$count = $q->fetch();

if ($count['c'] <= 1) {
	$errors[] = "Cannot remove that OpenID identity; at least one identity must be defined.";
	set_temporary_messages($messages);
	set_temporary_errors($errors);
	redirect(url_for('user#user_openid'));
}

$q = db()->prepare("SELECT * FROM openid_identities WHERE user_id=? AND id=? LIMIT 1");
$q->execute(array(user_id(), require_post("id")));
$identity = $q->fetch();

$q = db()->prepare("DELETE FROM openid_identities WHERE user_id=? AND id=?");
$q->execute(array(user_id(), require_post("id")));

$messages[] = "Removed OpenID identity '" . ($identity ? htmlspecialchars($identity['url']) : '<i>unknown</i>') . "'.";

set_temporary_messages($messages);
set_temporary_errors($errors);
redirect(url_for('user#user_openid'));
