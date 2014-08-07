<?php

define('FORCE_NO_RELATIVE', true);		// url_for() references need to be relative to the base path, not the js/ directory that this script is within

require(__DIR__ . "/../../../inc/content_type/json.php");		// to allow for appropriate headers etc
require(__DIR__ . "/../../../inc/global.php");
require(__DIR__ . "/../../../inc/cache.php");

function api_v1_graphs($graph) {
	$start_time = microtime(true);

	$result = array();
	$result['success'] = true;

	/**
	 * Graph rendering goes like this:
	 * 0. check graph rendering permissions
	 * 1. get raw graph data (from a {@link GraphRenderer} through {@link construct_graph_renderer()})
	 * 2. apply deltas as necessary
	 * 3. add technicals as necessary
	 * 4. strip dates outside of the requested ?days parameter (e.g. from extra_days)
	 * 5. construct heading and links
	 * 6. construct subheading and revise last_updated
	 * 7. return data
	 * that is, deltas and technicals are done on the server-side; not the client-side.
	 */
	$renderer = construct_graph_renderer($graph['graph_type'], $graph['arg0'], $graph['arg0_resolved']);

	// 0. check graph rendering permissions
	if ($renderer->requiresUser()) {
		if (!isset($graph['user_id']) || !$graph['user_id']) {
			throw new GraphException("No user specified for authenticated graph");
		}
		if (!isset($graph['user_hash']) || !$graph['user_hash']) {
			throw new GraphException("No user hash specified for authenticated graph");
		}

		$user = get_user($graph['user_id']);
		if (!$user) {
			throw new GraphException("No such user found");
		}
		$expected_hash = compute_user_graph_hash($user);
		if ($graph['user_hash'] !== $expected_hash) {
			throw new GraphException("Mismatched user hash");
		}

		if ($renderer->requiresAdmin()) {
			if (!$user['is_admin']) {
				throw new GraphException("Graph requires administrator privileges");
			}
		}
	}

	// 1. get raw graph data
	$data = $renderer->getData($graph['days']);
	$original_count = count($data['data']);

	// 2. apply deltas as necessary
	$data['data'] = calculate_graph_deltas($graph, $data['data'], false /* ignore_first_row */);

	// 3. add technicals as necessary
	// (only if there is at least one point of data, otherwise calculate_technicals() will throw an error)
	if ($renderer->canHaveTechnicals()) {
		$technicals = calculate_technicals($graph, $data['data'], $data['columns'], false /* ignore_first_row */);
		$data['columns'] = $technicals['headings'];
		$data['data'] = $technicals['data'];
	}

	// 4. discard early data
	$data['data'] = discard_early_data($data['data'], $graph['days']);
	$after_discard_count = count($data['data']);

	$result['columns'] = $data['columns'];
	$result['data'] = $data['data'];

	// clean up columns
	foreach ($result['columns'] as $key => $value) {
		$result['columns'][$key]['technical'] = isset($result['columns'][$key]['technical']) && $result['columns'][$key]['technical'] ? true : false;
		if ($result['columns'][$key]['technical']) {
			if (!isset($result['columns'][$key]['type'])) {
				$result['columns'][$key]['type'] = 'number';
			}
		}
	}

	$result['type'] = $renderer->getChartType();

	// 5. construct heading and links
	$result['heading'] = array(
		'label' => $renderer->getTitle(),
		'url' => $renderer->getURL(),
		'title' => $renderer->getLabel(),
	);

	if (isset($data['h1'])) {
		$result['h1'] = $data['h1'];
	}
	if (isset($data['h2'])) {
		$result['h2'] = $data['h2'];
	}

	// 6. construct subheading and revise last_updated
	if ($renderer->hasSubheading()) {
		$result['subheading'] = format_subheading_values_objects($graph, $data['data'], $data['columns']);
	}

	$result['lastUpdated'] = recent_format_html($data['last_updated']);
	$result['timestamp'] = iso_date();
	$result['classes'] = $renderer->getClasses();

	if (is_localhost()) {
		$result['_debug'] = $graph;
		$result['_debug']['data_discarded'] = $original_count - $after_discard_count;
	}

	// make sure that all 'number'-typed data is numeric
	foreach ($result['data'] as $i => $row) {
		foreach ($row as $key => $value) {
			$column = $result['columns'][$key + 1 /* first heading is key */];
			if ($column['type'] == 'number') {
				$result['data'][$i][$key] = (double) $value;

				if (is_localhost()) {
					$result['_debug']['number_formatted'] = true;
				}
			}
		}
	}

	$end_time = microtime(true);
	$time_diff = ($end_time - $start_time) * 1000;
	$result['time'] = graph_number_format($time_diff);

	// 7. return data
	return json_encode($result);
}

require(__DIR__ . "/../../../layout/templates.php");
require(__DIR__ . "/../../../layout/graphs.php");

$graph_type = require_get("graph_type");

// load graph data, which is also used to construct the hash
$config = array(
	'days' => require_get("days"),
	'delta' => require_get("delta", false),
	'arg0' => require_get('arg0', false),
	'arg0_resolved' => require_get('arg0_resolved', false),
	'user_id' => require_get('user_id', false),
	'user_hash' => require_get('user_hash', false),
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
function construct_graph_renderer($graph_type, $arg0, $arg0_resolved) {
	$bits = explode("_", $graph_type);
	if (count($bits) == 3) {
		$all_exchanges = get_all_exchanges();
		$cur1 = false;
		$cur2 = false;
		if (strlen($bits[1]) == 6) {
			$cur1 = substr($bits[1], 0, 3);
			$cur2 = substr($bits[1], 3);
			$cur1 = in_array($cur1, get_all_currencies()) ? $cur1 : false;
			$cur2 = in_array($cur1, get_all_currencies()) ? $cur2 : false;
		}

		if ($bits[2] == "daily" && $cur1 && $cur2 && isset($all_exchanges[$bits[0]])) {
			return new GraphRenderer_Ticker($bits[0], $cur1, $cur2);
		}

		if ($bits[2] == "markets" && $cur1 && $cur2 && $bits[0] == "average") {
			return new GraphRenderer_AverageMarketData($cur1, $cur2);
		}
	}

	switch ($graph_type) {
		case "external_historical":
			return new GraphRenderer_ExternalHistorical($arg0_resolved);

		case "admin_statistics":
			return new GraphRenderer_AdminStatistics();

		default:
			throw new NoGraphRendererException("Unknown graph to render '$graph_type'");
	}
}
