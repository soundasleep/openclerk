<?php

/**
 * Renders a single graph as HTML, which can then be loaded through AJAX
 * so the profile page load doesn't halt everything.
 *
 * This script is for graphs belonging to a user.
 */

require(__DIR__ . "/../inc/global.php");
require(__DIR__ . "/../layout/graphs.php");
require_login();

require(__DIR__ . "/../layout/templates.php");

$q = db()->prepare("SELECT graphs.* FROM graphs JOIN graph_pages ON graphs.page_id=graph_pages.id WHERE graphs.id=? AND graph_pages.user_id=?");
$q->execute(array(require_get("id"), user_id()));
$graph = $q->fetch();

if (!$graph) {
	throw new Exception("No graph " . require_get("id") . " for user " . user_id());
}

render_graph_actual($graph, false);

if (get_site_config('timed_sql') && is_admin()) {
	global $global_timed_sql;
	echo "\n<!-- SQL debug: \n " . print_r($global_timed_sql, true) . "\n-->";
}

performance_metrics_page_end();
