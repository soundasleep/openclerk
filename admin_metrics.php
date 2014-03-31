<?php

/**
 * Admin metrics page.
 */

require(__DIR__ . "/inc/global.php");
require_admin();

require(__DIR__ . "/layout/templates.php");
require(__DIR__ . "/layout/graphs.php");

$messages = array();
$errors = array();

page_header("Site Metrics", "page_admin_metrics", array('common_js' => true, 'jquery' => true, 'jsapi' => true));
$graph_count = 0;

?>

<h1>Site Metrics</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<div class="graph_collection">
	<?php
	$graph = array(
		'graph_type' => 'metrics_db_slow_queries',
		'width' => 8,
		'height' => 2,
		'page_order' => 0,
		// 'days' => 30,
		'delta' => '',
		'id' => $graph_count++,
		'public' => true,
	);

	render_graph($graph, true /* is not actually public, but the graph logic will take care of this */); 
	?>

	<?php 
	$graph = array(
		'graph_type' => 'metrics_curl_slow_urls',
		'width' => 8,
		'height' => 2,
		'page_order' => 0,
		// 'days' => 30,
		'delta' => '',
		'id' => $graph_count++,
		'public' => true,
	);

	render_graph($graph, true /* is not actually public, but the graph logic will take care of this */); 
	?>
</div>

<?php
page_footer();
