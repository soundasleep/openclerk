<?php

/**
 * This page displays external API historical data publically.
 */

require(__DIR__ . "/../inc/global.php");
require(__DIR__ . "/../layout/graphs.php");

require(__DIR__ . "/../layout/templates.php");

$messages = array();
$errors = array();

$type = require_get('type');

$titles = get_external_apis_titles();
if (!isset($titles[$type])) {
	set_temporary_errors(t("No such external API type ':type'.", array(':type' => htmlspecialchars($type))));
	redirect(url_for('external'));
}
$api_title = $titles[$type];

$graph = array(
	'graph_type' => 'external_historical',
	'width' => 8,
	'height' => 4,
	'page_order' => 0,
	'days' => 45,
	'delta' => '',
	'id' => 0,
	'arg0_resolved' => $type,
	'public' => true,
	'no_technicals' => true,
);

page_header(t("External API Status: :api_title", array(':api_title' => $api_title)), "page_external_historical", array('jsapi' => true));

?>
	<h1><?php echo ht("External API Status: :api_title", array(':api_title' => $api_title)); ?></h1>

	<p class="backlink">
		<a href="<?php echo htmlspecialchars(url_for('external')); ?>"><?php echo ht("< Back to External API Status"); ?></a>
	</p>

	<p>
		<?php /* TODO maybe add a link here, e.g. [BTC-E] ticker. TODO 'premium' should be labelled internal, not external */ ?>
		<?php echo t("This graph displays the status of the external :api_title, in terms of how many
		tasks were successful (in percent).", array(':api_title' => htmlspecialchars($api_title))); ?>
	</p>

	<div class="graph_collection">
		<?php render_graph_new($graph); ?>
	</div>
<?php

page_footer();
