<?php

/**
 * How the new graph render code works:
 * {@link new_render_graph()} generates the HTML on the page necessary to render the graph;
 * which uses graphs.js
 * which uses AJAX to load data, and timeouts to reload graphs
 * which then call the site API to load graph data
 * and then renders the graph using Google Graphs API (or whatever graphing framework we're using)
 */

/**
 * Render the HTML on the page necessary for rendering a graph to the user.
 * @param graph
 *	$graph = array(
 *		'graph_type' => $id,
 *		'width' => 8,
 *		'height' => 4,
 *		'page_order' => 0,
 *		'days' => $days,
 *		'id' => 0,
 *		'arg0_resolved' => $name,
 *		'delta' => $delta,
 *		'public' => true,
 *		'no_technicals' => true,
 *	);
 */
function new_render_graph($graph) {
	global $_rendered_graph_contents;
	if (!$_rendered_graph_contents) {
		?>
		<div id="graph_contents_template" style="display:none;">
			<h2 class="graph_title">
				<a href=""></a>
			</h2>
			<span class="subheading"></span>
			<span class="last-updated"></span>
			<div class="graph-target"><span class="status_loading">Loading...</span></div>
		</div>
		<?php
	}

	$graph_id = "graph_" . rand(0,0xffff);
	$graph['target'] = $graph_id;

	// TODO work these out properly
	$graph['computedWidth'] = 880;
	$graph['graphWidth'] = 880;
	$graph['computedHeight'] = 470;
	$graph['graphHeight'] = 440;
	?>
		<div id="<?php echo htmlspecialchars($graph_id); ?>" class="graph"></div>
		<script type="text/javascript">
			Graphs.render(<?php echo json_encode($graph); ?>);
		</script>
	<?php
}

$_rendered_graph_contents = false;
