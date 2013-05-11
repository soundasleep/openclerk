<?php

/**
 * This page displays historical data publically.
 */

require("inc/global.php");
require("layout/graphs.php");

require("layout/templates.php");

$messages = array();
$errors = array();

$historical_graphs = graph_types_public();

$id = require_get("id", false);
if ($id && isset($historical_graphs[$id])) {
	// we're displaying a specific graph

	page_header("Historical Data: " . $historical_graphs[$id]["heading"], "page_historical", array('common_js' => true, 'jsapi' => true));

	$graph = array(
		'graph_type' => $id,
		'width' => 8,
		'height' => 4,
		'page_order' => 0,
		'id' => 0,
	);

	?>
	<h1>Historical Data: <?php echo htmlspecialchars($historical_graphs[$id]["heading"]); ?></h1>

	<p class="backlink">
	<a href="<?php echo htmlspecialchars(url_for('historical')); ?>">&lt; Back to Historical Data</a>
	</p>

	<div class="graph_collection">
	<div class="graph graph_<?php echo htmlspecialchars($graph['graph_type']); ?>" id="graph<?php echo htmlspecialchars($graph['id']); ?>">
		<?php render_graph($graph, true /* is public */); ?>
	</div>
	</div>
	<?php

} else {

	// we want to display a list of all possible graphs

	page_header("Historical Data", "page_historical");

	?>

	<h1>Historical Data</h1>

	<ul class="historical_graphs">
	<?php foreach ($historical_graphs as $key => $def) {
		echo "<li><a href=\"" . htmlspecialchars(url_for('historical', array('id' => $key))) . "\">" . htmlspecialchars($def['title']) . "</a></li>\n";
	} ?>
	</ul>

	<?php

}

page_footer();
