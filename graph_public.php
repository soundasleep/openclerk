<?php

/**
 * Renders a single graph as HTML, which can then be loaded through AJAX
 * so the profile page load doesn't halt everything.
 *
 * This script is for graphs that do not belong to a user, such as external
 * status, public tickers and so on.
 */

require(__DIR__ . "/inc/global.php");
require(__DIR__ . "/layout/graphs.php");

require(__DIR__ . "/layout/templates.php");

// construct a public graph from our request parameters
$graph = array(
	'graph_type' => require_get("graph_type"),
	'width' => require_get("width"),
	'height' => require_get("height"),
	'page_order' => 0,
	'days' => require_get("days", false),
	'id' => require_get("id", 0),		// we need to have an id if we will have multiple public graphs per page (such as profile?securities=1)
	'arg0' => require_get("arg0", false),
	'arg0_resolved' => require_get("arg0_resolved", false),
	'no_technicals' => require_get("no_technicals", false),
	'delta' => require_get('delta', ''),
	'public' => true,
);
$permitted = get_permitted_deltas();
if (!isset($permitted[$graph['delta']])) {
	throw new Exception("Unsupported delta '" . htmlspecialchars($graph['delta']) . "'");
}

render_graph_actual($graph, true);
