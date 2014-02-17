<?php

/**
 * Issue #58: a separate include for add_graph javascript, so that it can be cached
 * appropriately.
 */

require(__DIR__ . "/../inc/content_type/js.php");		// to allow for appropriate headers etc
require(__DIR__ . "/../inc/global.php");
require(__DIR__ . "/../layout/graphs.php");

require_login();

// note that the contents of this file will change based on user, selected currencies etc;
// these parameters need to be encoded into a ?hash parameter, so that while this file can
// be cached, it is correctly reloaded when necessary.
allow_cache();

?>

function graph_types() {
	return [
<?php foreach (graph_types() as $id => $graph) {
	if (!(isset($graph['hide']) && $graph['hide'])) {
		// we don't want to display graph types that we aren't interested in
		$arg0 = (isset($graph['arg0']) && $graph['arg0']) ? $graph['arg0'] : false;
		$arg0_values = $arg0 ? $arg0(isset($graph['param0']) ? $graph['param0'] : false, isset($graph['param1']) ? $graph['param1'] : false) : false;
		if ($arg0_values) {
			// need to convert from array of (id => value) to a list of {id, value}, because JS
			// sorts by id whereas PHP sorts by insertion order
			$result = array();
			foreach ($arg0_values as $key => $value) {
				$result[] = array($key, $value);
			}
			$arg0_values = $result;
		}
		if (!($arg0 && !$arg0_values)) {
			// we also don't want to display graph types that need arguments, but there aren't any
			if (isset($graph['category']) && $graph['category']) {
				echo "{ 'category' : " . json_encode($graph['title']) . " },\n";
			} else {
				echo "{ 'id' : " . json_encode($id) . ", 'title' : " . json_encode($graph['title']) . ", 'description' : " .  json_encode($graph['description']) .
					((isset($graph['technical']) && $graph['technical']) ? ", 'technical': true" : "") .
					((isset($graph['arg0_title']) && $graph['arg0_title']) ? ", 'arg0_title': " . json_encode($graph['arg0_title']) : "") .
					($arg0 ? ", 'arg0': " . json_encode($arg0_values) : "") .
					", 'string0': " . ((isset($graph['string0']) && $graph['string0']) ? json_encode($graph['string0']) : "null") .
					", 'days': " . json_encode(isset($graph['days'])) . ", 'delta': " . json_encode(isset($graph['delta'])) . "},\n";
			}
		}
	}
} ?>
	];
}

function graph_technical_types() {
	return [
<?php foreach (graph_technical_types() as $id => $data) {
	echo "{ 'id' : '" . htmlspecialchars($id) . "', 'title' : '" . htmlspecialchars($data['title']) . "'" .
			", 'description' : " . json_encode($data['description']) .
			", 'premium' : " . ($data['premium'] ? "true" : "false") .
			", 'period' : " . ($data['period'] ? "true" : "false") . "},\n";
} ?>
	];
}
