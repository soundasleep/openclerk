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
	if (!in_array($ticker['currency1'], get_all_currencies())) {
		continue;
	}
	if (!in_array($ticker['currency2'], get_all_currencies())) {
		continue;
	}

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
			'days' => 90,
			'id' => $graph_id++,
			'public' => true,
			'delta' => "",
			'no_technicals' => true,
			"arg0" => true,
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
			'days' => 90,
			'id' => $graph_id++,
			'public' => true,
			'delta' => "",
			'no_technicals' => true,
			"arg0" => true,
		);
	?>
	<?php render_graph($graph, true /* is public */); ?>
</div>

<?php
$all_main_currencies = array();
foreach ($pairs as $pair) {
	$all_main_currencies[$pair['currency1']] = $pair['currency1'];
}
?>

<div class="currencies">
	<h2>Other market averages</h2>

	<div class="tabs" id="tabs_average">
		<ul class="tab_list">
			<?php /* each <li> must not have any whitespace between them otherwise whitespace will appear when rendered */ ?>
			<?php foreach ($all_main_currencies as $c) {
				echo "<li id=\"tab_average_" . $c . "\"><span class=\"currency_name_" . $c . "\">" . htmlspecialchars(get_currency_abbr($c)) . "</span></li>";
			} ?>
		</ul>

		<ul class="tab_groups">
		<?php $first_tab = true;
		foreach ($all_main_currencies as $c) { ?>
		<li id="tab_average_<?php echo $c; ?>_tab"<?php echo $first_tab ? "" : " style=\"display:none;\""; ?> class="tab">
			<h3><?php echo get_currency_name($c); ?> market averages</h3>
			<ul>
			<?php
			foreach ($pairs as $pair) {
				if ($pair['currency1'] != $c) {
					continue;
				}

				$selected = ($currency1 == $pair['currency1'] && $currency2 == $pair['currency2']);
				echo "<li" . ($selected ? " class=\"selected\"" : "") . ">";
				echo "<a href=\"" . htmlspecialchars(url_for('average', array('currency1' => $pair['currency1'], 'currency2' => $pair['currency2']))) . "\">";
				echo get_currency_abbr($pair['currency1']) . "/" . get_currency_abbr($pair['currency2']);
				echo "</a>";
				echo " (" . number_format($pair['market_count']) . ")";
				echo "</li>\n";
			}
			?>
			</ul>
		</li>
		<?php $first_tab = false;
		} ?>
		</ul>
	</div>

	<script type="text/javascript">
	$(document).ready(function() {
		initialise_tabs('#tabs_average');
	});
	</script>
</div>

<?php

page_footer();
