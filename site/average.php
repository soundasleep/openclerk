<?php

/**
 * This page displays market average data publically.
 */

require(__DIR__ . "/../inc/global.php");
require(__DIR__ . "/../layout/graphs.php");

require(__DIR__ . "/../layout/templates.php");

$messages = array();
$errors = array();

// get all average pairs
$q = db()->prepare("SELECT * FROM ticker_recent AS t
		JOIN average_market_count AS m ON t.currency1=m.currency1 AND t.currency2=m.currency2
		WHERE exchange=?
		ORDER BY t.currency1 ASC, t.currency2 ASC");
$q->execute(array("average"));
$pairs = array();
while ($ticker = $q->fetch()) {
	// TODO strip out currencies we aren't officially tracking yet

	$key = $ticker['currency1'] . $ticker['currency2'];
	$pairs[$key] = $ticker;
}

// if none is selected, use defaults
$currency1 = require_get("currency1", "usd");
$currency2 = require_get("currency2", "btc");
if (!in_array($currency1, get_all_currencies())) {
	$currency1 = "usd";
}
if (!in_array($currency2, get_all_currencies())) {
	$currency1 = "btc";
}

if (!isset($pairs[$currency1 . $currency2])) {
	$currency1 = "usd";
	$currency2 = "btc";
}

page_header("Market Average: " . get_currency_name($currency1) . "/" . get_currency_name($currency2), "page_average", array('jsapi' => true));
$graph_id = 0;

?>

<h1>Market Average: <?php echo get_currency_abbr($currency1); ?>/<?php echo get_currency_abbr($currency2); ?></h1>

<?php require_template("average"); ?>

<div class="graph_collection">
	<div class="market-data">
	<?php
		$graph = array(
			'graph_type' => "average_" . $currency1 . $currency2 . "_markets",
			'width' => 4,
			'height' => 2,
			'page_order' => 0,
			'days' => 45,
			'id' => $graph_id++,
			'public' => true,
			'delta' => "",
			'no_technicals' => true,
		);
	?>
	<?php render_graph($graph, true /* is public */); ?>
	</div>

	<?php
		$graph = array(
			'graph_type' => "average_" . $currency1 . $currency2 . "_daily",
			'width' => 4,
			'height' => 2,
			'page_order' => 0,
			'days' => 45,
			'id' => $graph_id++,
			'public' => true,
			'delta' => "",
			'no_technicals' => true,
		);
	?>
	<?php render_graph($graph, true /* is public */); ?>
</div>

<div class="currencies">
	<h2>Other market averages</h2>

	<?php
	$last_currency = false;

	foreach ($pairs as $pair) {
		if ($pair['currency1'] != $last_currency) {
			if ($last_currency !== false) {
				echo "</ul>\n";
			}
			echo "<h3>" . get_currency_name($pair['currency1']) . "</h3>\n";
			echo "<ul>\n";
		}
		$last_currency = $pair['currency1'];

		$selected = ($currency1 == $pair['currency1'] && $currency2 == $pair['currency2']);
		echo "<li" . ($selected ? " class=\"selected\"" : "") . ">";
		echo "<a href=\"" . htmlspecialchars(url_for('average', array('currency1' => $pair['currency1'], 'currency2' => $pair['currency2']))) . "\">";
		echo get_currency_abbr($pair['currency1']) . "/" . get_currency_abbr($pair['currency2']);
		echo "</a>";
		echo " (" . number_format($pair['market_count']) . ")";
		echo "</li>\n";
	}

	echo "</ul>";
	?>
</div>

<?php

page_footer();
