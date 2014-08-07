<?php

/**
 * Admin metrics page.
 */

require(__DIR__ . "/../inc/global.php");
require_admin();

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../layout/graphs.php");

$messages = array();
$errors = array();

page_header("Site Metrics", "page_admin_metrics", array('jsapi' => true));
$graph_count = 0;

?>

<h1>Site Metrics</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<div class="graph_collection">
	<?php
	$graph = array(
		'graph_type' => 'metrics_db_slow_queries_graph',
		'width' => 4,
		'height' => 2,
		'page_order' => 0,
		'days' => 'year',
		'delta' => '',
		'id' => $graph_count++,
		'public' => true,
	);

	render_graph_new($graph, true /* is not actually public, but the graph logic will take care of this */);
	?>

	<?php
	$graph = array(
		'graph_type' => 'metrics_curl_slow_urls_graph',
		'width' => 4,
		'height' => 2,
		'page_order' => 0,
		'days' => 'year',
		'delta' => '',
		'id' => $graph_count++,
		'public' => true,
	);

	render_graph_new($graph, true /* is not actually public, but the graph logic will take care of this */);
	?>

	<h1 style="clear:both;">Tasks</h1>

	<?php
	$graph = array(
		'graph_type' => 'metrics_slow_jobs_graph',
		'width' => 4,
		'height' => 2,
		'page_order' => 0,
		'days' => 'year',
		'delta' => '',
		'id' => $graph_count++,
		'public' => true,
	);

	render_graph_new($graph, true /* is not actually public, but the graph logic will take care of this */);
	?>

	<?php
	$graph = array(
		'graph_type' => 'metrics_slow_jobs_database_graph',
		'width' => 4,
		'height' => 2,
		'page_order' => 0,
		'days' => 'year',
		'delta' => '',
		'id' => $graph_count++,
		'public' => true,
	);

	render_graph_new($graph, true /* is not actually public, but the graph logic will take care of this */);
	?>

	<?php
	$graph = array(
		'graph_type' => 'metrics_slow_pages_graph',
		'width' => 4,
		'height' => 2,
		'page_order' => 0,
		'days' => 'year',
		'delta' => '',
		'id' => $graph_count++,
		'public' => true,
	);

	render_graph_new($graph, true /* is not actually public, but the graph logic will take care of this */);
	?>

	<?php
	$graph = array(
		'graph_type' => 'metrics_slow_pages_database_graph',
		'width' => 4,
		'height' => 2,
		'page_order' => 0,
		'days' => 'year',
		'delta' => '',
		'id' => $graph_count++,
		'public' => true,
	);

	render_graph_new($graph, true /* is not actually public, but the graph logic will take care of this */);
	?>

	<?php
	$graph = array(
		'graph_type' => 'metrics_slow_graphs_graph',
		'width' => 4,
		'height' => 2,
		'page_order' => 0,
		'days' => 'year',
		'delta' => '',
		'id' => $graph_count++,
		'public' => true,
	);

	render_graph_new($graph, true /* is not actually public, but the graph logic will take care of this */);
	?>

	<?php
	$graph = array(
		'graph_type' => 'metrics_slow_graphs_database_graph',
		'width' => 4,
		'height' => 2,
		'page_order' => 0,
		'days' => 'year',
		'delta' => '',
		'id' => $graph_count++,
		'public' => true,
	);

	render_graph_new($graph, true /* is not actually public, but the graph logic will take care of this */);
	?>

	<?php
	$graph = array(
		'graph_type' => 'metrics_slow_graphs_count_graph',
		'width' => 4,
		'height' => 2,
		'page_order' => 0,
		'days' => 'year',
		'delta' => '',
		'id' => $graph_count++,
		'public' => true,
	);

	render_graph_new($graph, true /* is not actually public, but the graph logic will take care of this */);
	?>

	<?php
	$graph = array(
		'graph_type' => 'metrics_jobs_frequency_graph',
		'width' => 4,
		'height' => 2,
		'page_order' => 0,
		'days' => 'year',
		'delta' => '',
		'id' => $graph_count++,
		'public' => true,
	);

	render_graph_new($graph, true /* is not actually public, but the graph logic will take care of this */);
	?>

	<h1 style="clear:both;">System Load</h1>

	<?php
	$graph = array(
		'graph_type' => 'statistics_system_load',
		'width' => 4,
		'height' => 2,
		'page_order' => 0,
		'days' => 'year',
		'delta' => '',
		'id' => $graph_count++,
		'public' => true,
	);

	render_graph_new($graph, true /* is not actually public, but the graph logic will take care of this */);
	?>

	<?php
	$graph = array(
		'graph_type' => 'statistics_db_system_load',
		'width' => 4,
		'height' => 2,
		'page_order' => 0,
		'days' => 'year',
		'delta' => '',
		'id' => $graph_count++,
		'public' => true,
	);

	render_graph_new($graph, true /* is not actually public, but the graph logic will take care of this */);
	?>
</div>

<?php
page_footer();
