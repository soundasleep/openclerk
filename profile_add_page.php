<?php

require("inc/global.php");
require("layout/graphs.php");
require_login();

// adding a new page?
$title = require_post("title");

$title = substr($title, 0, 64); // limit to 64 characters
if (!$title) {
	$title = "Untitled";
}

// it's OK - let's add a new one
// first get the highest page order so far on this page
$q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=? ORDER BY page_order DESC LIMIT 1");	// including is_removed (in case of restore)
$q->execute(array(user_id()));
$highest = $q->fetch();
$new_order = $highest ? ($highest['page_order'] + 1) : 1;

// now insert it
$q = db()->prepare("INSERT INTO graph_pages SET user_id=:user_id, title=:title, page_order=:page_order");
$q->execute(array(
	'user_id' => user_id(),
	'title' => $title,
	'page_order' => $new_order,
));
$new_page_id = db()->lastInsertId();

// redirect
redirect(url_for('profile', array('page' => $new_page_id)));
