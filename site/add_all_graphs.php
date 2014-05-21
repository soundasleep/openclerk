<?php

/**
 * An admin tool to generate a page with example graphs of every type.
 */

require(__DIR__ . "/../inc/global.php");
require(__DIR__ . "/../layout/graphs.php");
require_login();
require_admin();

$page_id = require_post("page");

$messages = array();
$errors = array();

// check that we own this page
$q = db()->prepare("SELECT * FROM graph_pages WHERE id=? AND user_id=?");
$q->execute(array($page_id, user_id()));
if (!$q->fetch()) {
	throw new Exception("You do not own that graph page.");
}

// delete all old graphs
$q = db()->prepare("DELETE FROM graphs WHERE page_id=?");
$q->execute(array($page_id));

// now go through all graphs
$count = 0;
foreach (graph_types() as $key => $graph_type) {
	if (isset($graph_type['category']) && $graph_type['category']) {
		// add a new heading
		$graph = array(
			'page_id' => $page_id,
			'graph_type' => 'heading',
			'arg0' => 0,
			'width' => 1,
			'height' => 1,
			'page_order' => $count,
			'days' => 0,
			'string0' => "Category: " . $graph_type['title'],
		);
	} else {
		$graph = array(
			'page_id' => $page_id,
			'graph_type' => $key,
			'arg0' => 0,
			'width' => isset($graph_type['default_width']) ? $graph_type['default_width'] : get_site_config('default_user_graph_width'),
			'height' => isset($graph_type['default_height']) ? $graph_type['default_height'] : get_site_config('default_user_graph_height'),
			'page_order' => $count,
			'days' => 0,
			'string0' => '',
		);
	}

	$q = db()->prepare("INSERT INTO graphs SET page_id=:page_id, graph_type=:graph_type, arg0=:arg0, width=:width, height=:height, page_order=:page_order, days=:days, string0=:string0");
	$q->execute($graph);

	$count++;

}

// redirect to this page
$messages[] = "Reset graph page with " . plural("example graph", $count) . ".";

redirect(url_for('profile', array('page' => $page_id)));
