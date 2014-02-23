<?php

define('FORCE_NO_RELATIVE', true);		// url_for() references need to be relative to the base path, not the js/ directory that this script is within

require(__DIR__ . "/../../inc/content_type/json.php");		// to allow for appropriate headers etc
require(__DIR__ . "/../../inc/global.php");

$result = array();
$result['currencies'] = array();
foreach (get_all_currencies() as $cur) {
	$result['currencies'][] = array(
		'code' => $cur,
		'abbr' => get_currency_abbr($cur),
		'name' => get_currency_name($cur)
	);
}

require(__DIR__ . "/../../inc/api.php");
$result['rates'] = api_get_all_rates();

echo json_encode($result);
