<?php

/**
 * This page displays external API historical data publically.
 */

require("inc/global.php");
require("layout/graphs.php");

require("layout/templates.php");

$messages = array();
$errors = array();

$type = require_get('type');

$titles = get_external_apis_titles();
if (!isset($titles[$type])) {
	set_temporary_errors("No such external API type '" . htmlspecialchars($type) . "'");
	redirect(url_for('external'));
}
$api_title = $titles[$type];

$graph = array(
	'graph_type' => 'external_historical',
	'width' => 8,
	'height' => 4,
	'page_order' => 0,
	// 'days' => 30,
	'id' => 0,
	'arg0_resolved' => $type,
	'public' => true,
);

page_header('External API Status: ' . htmlspecialchars($api_title), "page_external_historical", array('common_js' => true, 'jsapi' => true));

?>
	<h1>External API Status: <?php echo htmlspecialchars($api_title); ?></h1>

	<p class="backlink">
		<a href="<?php echo htmlspecialchars(url_for('external')); ?>">&lt; Back to External API Status</a>
	</p>

	<p>
		<?php /* TODO maybe add a link here, e.g. [BTC-E] ticker */ ?>
		This graph displays the status of the <?php echo htmlspecialchars($api_title); ?> external API, in terms of how many
		tasks were successful (in percent).
	</p>

	<div class="graph_collection">
	<div class="graph graph_<?php echo htmlspecialchars($graph['graph_type']); ?>" id="graph<?php echo htmlspecialchars($graph['id']); ?>">
		<?php render_graph($graph, true /* is public */); ?>
	</div>
	</div>
<?php

page_footer();
