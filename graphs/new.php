<?php

/**
 * How the new graph render code works:
 * {@link render_graph_new()} generates the HTML on the page necessary to render the graph;
 * which uses graphs.js
 * which uses AJAX to load data, and timeouts to reload graphs
 * which then call the site API to load graph data
 * and then renders the graph using Google Graphs API (or whatever graphing framework we're using)
 */

/**
 * Render the HTML on the page necessary for rendering a graph to the user.
 * @param $graph = array(
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
 * @param $include_user_hash if true, include user_id and user_hash in the graph data, necessary for
 *				graphs that require user authentication; default is false
 */
function render_graph_new($graph, $include_user_hash = false) {
	global $_rendered_graph_contents;
	if (!$_rendered_graph_contents) {
		?>
		<div id="graph_contents_template" style="display:none;">
			<div class="graph_headings">
				<h1 class="h1"></h1>
				<h2 class="h2"></h2>
				<h2 class="graph_title">
					<a href=""></a>
				</h2>
				<span class="subheading"></span>
				<span class="last-updated"></span>
			</div>
			<div class="graph-target"><span class="status_loading"><?php echo ht("Loading..."); ?></span></div>
			<div class="graph_extra extra" style="display:none;"><a href="#"></a></span></div>
			<div class="admin-stats-wrapper hide-admin"><span class="admin-stats render_time"></span></div>
		</div>
		<div id="graph_table_template" class="overflow_wrapper" style="display:none;">
			<table class="standard graph_table">
			</table>
		</div>
		<?php
	}

	$graph_id = "graph_" . rand(0,0xffff);
	$graph['target'] = $graph_id;

	$graph['graphWidth'] = get_site_config('default_graph_width') * $graph['width'];
	$graph['computedWidth'] = $graph['graphWidth'];
	$graph['graphHeight'] = get_site_config('default_graph_height') * $graph['height'];
	$graph['computedHeight'] = $graph['graphHeight'] + 30;

	// if we are logged in, also provide the user ID and computed user hash, to verify that we can
	// correctly access this graph (also means that we don't have to initialise sessions on the API)
	if ($include_user_hash && user_logged_in()) {
		$graph['user_id'] = user_id();
		$graph['user_hash'] = compute_user_graph_hash(get_user(user_id()));
	}

	// 'overflow: hidden;' is to fix a Chrome rendering bug
	?>
		<div id="<?php echo htmlspecialchars($graph_id); ?>" class="graph" style="overflow: hidden;"></div>
		<script type="text/javascript">
			Graphs.render(<?php echo json_encode($graph); ?>);
		</script>
	<?php
}

$_rendered_graph_contents = false;

class NoGraphRendererException extends GraphException { }
class RenderGraphException extends GraphException { }

function compute_user_graph_hash($user) {
	return md5(get_site_config('user_graph_hash_salt') . ":" . $user['id'] . ":" . $user['last_login']);
}

/**
 * Helper function that converts a {@code graph_type} to a GraphRenderer
 * object, which we can then use to get raw graph data and format it as necessary.
 */
function construct_graph_renderer($graph_type, $arg0, $arg0_resolved) {
	$bits = explode("_", $graph_type);
	$all_exchanges = get_all_exchanges();
	if (count($bits) == 3) {
		$cur1 = false;
		$cur2 = false;
		if (strlen($bits[1]) == 6) {
			$cur1 = substr($bits[1], 0, 3);
			$cur2 = substr($bits[1], 3);
			$cur1 = in_array($cur1, get_all_currencies()) ? $cur1 : false;
			$cur2 = in_array($cur1, get_all_currencies()) ? $cur2 : false;
		}

		if ($bits[2] == "daily" && $cur1 && $cur2 && isset($all_exchanges[$bits[0]])) {
			return new GraphRenderer_Ticker($bits[0], $cur1, $cur2);
		}

		if ($bits[2] == "markets" && $cur1 && $cur2 && $bits[0] == "average") {
			return new GraphRenderer_AverageMarketData($cur1, $cur2);
		}

		if ($bits[0] == "composition" && in_array($bits[1], get_all_currencies())) {
			switch ($bits[2]) {
				case "pie":
					return new GraphRenderer_CompositionPie($bits[1]);
				case "table":
					return new GraphRenderer_CompositionTable($bits[1]);
				case "daily":
					return new GraphRenderer_CompositionGraph($bits[1]);
				case "stacked":
					return new GraphRenderer_CompositionStacked($bits[1]);
				case "proportional":
					return new GraphRenderer_CompositionProportional($bits[1]);
			}
		}

		if ($bits[0] == "total" && in_array($bits[1], get_all_currencies()) && $bits[2] == "daily") {
			return new GraphRenderer_SummaryGraph('total' . $bits[1], $bits[1]);
		}

		if ($bits[0] == "hashrate" && in_array($bits[1], get_all_currencies()) && $bits[2] == "daily") {
			return new GraphRenderer_SummaryGraphHashrate('totalmh_' . $bits[1], $bits[1]);
		}

	}

	if (count($bits) >= 2) {
		if (substr($bits[0], 0, strlen("all2")) == "all2" || substr($bits[0], 0, strlen("crypto2")) == "crypto2") {
			$cur = substr($bits[0], -3);
			if (in_array($cur, get_all_currencies())) {
				if (count($bits) == 3 && $bits[2] == "daily" && isset($all_exchanges[$bits[1]])) {
					// e.g. all2nzd_bitnz_daily
					return new GraphRenderer_SummaryGraphConvertedExchange($bits[0] . "_" . $bits[1], $cur);
				}
				if (count($bits) == 2 && $bits[1] == "daily") {
					// e.g. crypto2ltc_daily
					return new GraphRenderer_SummaryGraphConvertedCrypto($bits[0], $cur);
				}
			}
		}

	}

	if (count($bits) >= 2 && $bits[0] == "metrics") {
		$possible = GraphRenderer_AdminMetrics::getMetrics();
		$bits_two = explode("_", $graph_type, 2);
		if (isset($possible[$bits_two[1]])) {
			return new GraphRenderer_AdminMetrics($bits_two[1]);
		}
	}

	switch ($graph_type) {
		case "btc_equivalent":
			return new GraphRenderer_EquivalentPieBTC();

		case "balances_table":
			return new GraphRenderer_BalancesTable();

		case "external_historical":
			return new GraphRenderer_ExternalHistorical($arg0_resolved);

		case "admin_statistics":
			return new GraphRenderer_AdminStatistics();

		case "statistics_queue":
			return new GraphRenderer_StatisticsQueue();

		case "statistics_system_load":
			return new GraphRenderer_StatisticsSystemLoad("");

		case "statistics_db_system_load":
			return new GraphRenderer_StatisticsSystemLoad("db_");

		default:
			throw new NoGraphRendererException("Unknown graph to render '$graph_type'");
	}
}
