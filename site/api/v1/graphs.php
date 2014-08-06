<?php

define('FORCE_NO_RELATIVE', true);		// url_for() references need to be relative to the base path, not the js/ directory that this script is within

require(__DIR__ . "/../../../inc/content_type/json.php");		// to allow for appropriate headers etc
require(__DIR__ . "/../../../inc/global.php");
require(__DIR__ . "/../../../inc/cache.php");

function api_v1_graphs($graph) {
	$result = array();
	$result['success'] = true;

	/**
	 * Graph rendering goes like this:
	 * 1. get raw graph data (from a {@link GraphRenderer} through {@link construct_graph_renderer()})
	 * 2. apply deltas as necessary
	 * 3. add technicals as necessary
	 * 4. strip dates outside of the requested ?days parameter (e.g. from extra_days)
	 * 5. construct heading and links
	 * 6. construct subheading and revise last_updated
	 * 7. return data
	 * that is, deltas and technicals are done on the server-side; not the client-side.
	 */
	$renderer = construct_graph_renderer($graph['graph_type']);
	$data = $renderer->getData($graph['days']);
	$original_count = count($data['data']);

	// 2. apply deltas as necessary
	$data['data'] = calculate_graph_deltas($graph, $data['data'], false /* ignore_first_row */);

	// 3. add technicals as necessary
	// (only if there is at least one point of data, otherwise calculate_technicals() will throw an error)
	$technicals = calculate_technicals($graph, $data['data'], $data['columns'], false /* ignore_first_row */);
	$data['columns'] = $technicals['headings'];
	$data['data'] = $technicals['data'];

	// 4. discard early data
	$data['data'] = discard_early_data($data['data'], $graph['days']);
	$after_discard_count = count($data['data']);

	$result['columns'] = $data['columns'];
	$result['data'] = $data['data'];

	$result['type'] = 'linechart';

	// 5. construct heading and links
	$result['heading'] = array(
		'label' => $renderer->getTitle(),
		'url' => $renderer->getURL(),
		'title' => $renderer->getLabel(),
	);

	// 6. construct subheading and revise last_updated
	$result['subheading'] = format_subheading_values_objects($graph, $data['data'], $data['columns']);
	$result['lastUpdated'] = recent_format_html($data['last_updated']);

	$result['timestamp'] = iso_date();

	if (is_localhost()) {
		$result['_debug'] = $graph;
		$result['_debug']['data_discarded'] = $original_count - $after_discard_count;
	}

	// make sure that all data is numeric
	foreach ($result['data'] as $i => $row) {
		foreach ($row as $key => $value) {
			$result['data'][$i][$key] = (double) $value;
		}
	}

	// 7. return data
	return json_encode($result);
}

require(__DIR__ . "/../../../graphs/util.php");
require(__DIR__ . "/../../../graphs/render.php");
require(__DIR__ . "/../../../graphs/types.php");
require(__DIR__ . "/../../../layout/templates.php");

$graph_type = require_get("graph_type");

// load graph data, which is also used to construct the hash
$config = array(
	'days' => require_get("days"),
	'delta' => require_get("delta", false),
	'arg0' => require_get('arg0', false),
	'arg0_resolved' => require_get('arg0_resolved', false),
	// in this interface, we only support rendering one technical on one graph
	// (although the technicals interface supports multiple)
	'technical' => require_get('technical', false),
	'technical_period' => require_get('technical_period', false),
);
$hash = substr(implode(',', $config), 0, 32);

// limit 'days' parameter as necessary
$get_permitted_days = get_permitted_days();
if (!isset($get_permitted_days[$config['days']])) {
	throw new GraphException("Invalid days '" . $config['days'] . "'");
}

// and then restructure as necessary away from hash
$config['graph_type'] = require_get('graph_type');
$config['hash'] = $hash;
if ($config['technical']) {
	$config['technicals'] = array(array('technical_type' => $config['technical'], 'technical_period' => $config['technical_period']));
}

$seconds = 60;
allow_cache($seconds);		// allow local cache for up to 60 seconds
echo compile_cached('api/rates/' . $graph_type, $hash /* hash */, $seconds /* cached up to seconds */, 'api_v1_graphs', array($config));

performance_metrics_page_end();

class NoGraphRendererException extends GraphException { }

/**
 * Helper function to mark strings that need to be translated on the client-side.
 */
function ct($s) {
	return $s;
}


/**
 * Helper function that converts a {@code graph_type} to a GraphRenderer
 * object, which we can then use to get raw graph data and format it as necessary.
 */
function construct_graph_renderer($graph_type) {
	$bits = explode("_", $graph_type);
	if (count($bits) == 3) {
		$all_exchanges = get_all_exchanges();
		if ($bits[2] == "daily" && strlen($bits[1]) == 6 && isset($all_exchanges[$bits[0]])) {
			$cur1 = substr($bits[1], 0, 3);
			$cur2 = substr($bits[1], 3);
			if (in_array($cur1, get_all_currencies()) && in_array($cur2, get_all_currencies())) {
				return new GraphRenderer_Ticker($bits[0], $cur1, $cur2);
			}
		}
	}

	switch ($graph_type) {
		default:
			throw new NoGraphRendererException("Unknown graph to render '$graph_type'");
	}
}
