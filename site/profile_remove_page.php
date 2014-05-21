<?php

require(__DIR__ . "/../inc/global.php");
require(__DIR__ . "/../layout/graphs.php");
require_login();

// removing an existing page?
$page_id = require_post("page");
$confirm = require_post("confirm", false);

if (!$confirm) {
	// we're not deleting anything
	redirect(url_for('profile', array('page' => $page_id)));
}

// make sure it's our page
$q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=? AND id=?");
$q->execute(array(user_id(), $page_id));
if (!$q->fetch()) {
	throw new Exception(t("Cannot find page :id", array(':id' => htmlspecialchars($page_id))));
}

// delete it by hiding it
$q = db()->prepare("UPDATE graph_pages SET updated_at=NOW(),is_removed=1 WHERE user_id=? AND id=? LIMIT 1");
$q->execute(array(user_id(), $page_id));

// redirect to our home page, which will show the first page or none
redirect(url_for('profile'));
