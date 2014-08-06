<?php

define('FORCE_NO_RELATIVE', true);		// url_for() references need to be relative to the base path, not the js/ directory that this script is within

require(__DIR__ . "/../../../inc/content_type/json.php");		// to allow for appropriate headers etc
require(__DIR__ . "/../../../inc/global.php");
require(__DIR__ . "/../../../inc/cache.php");

function api_v1_graphs($graph) {
	$result = array();

	// TODO get actual graph data
	$result['columns'] = array();
	$result['columns'][] = array('type' => 'date', 'title' => 'Date');
	$result['columns'][] = array('type' => 'number', 'title' => 'NZD/BTC Bid');
	$result['columns'][] = array('type' => 'number', 'title' => 'NZD/BTC Ask');

	$result['data'] = array();
	$result['data']['2014-07-24'] = array(710, 720.9);
	$result['data']['2014-05-28'] = array(680, 693);
	$result['data']['2014-04-21'] = array(550, 593);
	$result['data']['2014-03-28'] = array(440, 493);

	$result['type'] = 'linechart';

	$result['heading'] = array(
		'label' => 'BitNZ NZD/BTC',
		'url' => 'historical?id=bitnz_nzdbtc_daily&amp;days=180',
		'title' => 'View historical data',
	);
	$result['subheading'] = '<span title="710">710</span> / <span title="720.9">720.9</span>';
	$result['lastUpdated'] = "<span title=\"2014-07-24T11:43:55+12:00\">13 days ago</span>";

	$result['timestamp'] = iso_date();

	return json_encode($result);
}

$graph_type = require_get("graph_type");

allow_cache(60);		// allow local cache for up to 60 seconds
echo compile_cached('api/rates/' . $graph_type, 'v1' /* hash */, 60 /* cached up to seconds */, 'api_v1_graphs', $_GET /* TODO maybe not use $_GET but use require_get */);

performance_metrics_page_end();
