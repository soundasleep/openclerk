<?php

define('FORCE_NO_RELATIVE', true);		// url_for() references need to be relative to the base path, not the js/ directory that this script is within

require(__DIR__ . "/../../../inc/content_type/json.php");		// to allow for appropriate headers etc
require(__DIR__ . "/../../../inc/global.php");
require(__DIR__ . "/../../../inc/cache.php");

function api_v1_rates() {
	$result = array();
	$result['currencies'] = array();
	foreach (get_all_currencies() as $cur) {
		$result['currencies'][] = array(
			'code' => $cur,
			'abbr' => get_currency_abbr($cur),
			'name' => get_currency_name($cur),
			'fiat' => is_fiat_currency($cur),
		);
	}

	require(__DIR__ . "/../../../inc/api.php");
	$result['rates'] = api_get_all_rates();

	return json_encode($result);
}

allow_cache(60);		// allow local cache for up to 60 seconds
echo compile_cached('api/rates', 'v1' /* hash */, 60 /* cached up to seconds */, 'api_v1_rates');

performance_metrics_page_end();
