<?php

require(__DIR__ . "/../graphs/util.php");
require(__DIR__ . "/../graphs/types.php");
require(__DIR__ . "/../graphs/render.php");
require(__DIR__ . "/../graphs/output.php");

/**
 * Load technicals information for a graph; returns an array
 * (which is intended to be inserted as 'technicals' back into the $graph).
 */
function load_technicals($graph, $is_public) {

	$graph_types = $is_public ? graph_types_public() : graph_types();
	if (!isset($graph_types[$graph['graph_type']])) {
		// this should never happen because graph_types should be checked before load_technicals is called
		throw new Exception("Could not load graph_type in load_technicals: this should never happen");
	}

	if (!(isset($graph['no_technicals']) && $graph['no_technicals']) && isset($graph_types[$graph['graph_type']]['technical']) && $graph_types[$graph['graph_type']]['technical']) {
		$q = db()->prepare("SELECT * FROM graph_technicals WHERE graph_id=?");
		$q->execute(array($graph['id']));
		return $q->fetchAll();
	}
	return array();

}

/**
 * Renders a particular graph.
 */
function render_graph($graph, $is_public = false) {

	echo "<div class=\"graph graph_" . htmlspecialchars($graph['graph_type']) . "\"";
	echo " id=\"graph" . htmlspecialchars($graph['id']) . "\"";
	// unfortunately necessary to fix Chrome rendering bugs (issue #46)
	echo " style=\"overflow: hidden; width: " . ((get_site_config('default_graph_width') * $graph['width'])) . "px; height: " . ((get_site_config('default_graph_height') * $graph['height']) + 30) . "px;\">";

	$graph_types = $is_public ? graph_types_public() : graph_types();
	if (!isset($graph_types[$graph['graph_type']])) {
		// let's not crash with an exception, let's just display an error
		render_graph_controls($graph);
		render_text($graph, "Unknown graph type " . htmlspecialchars($graph['graph_type']));
		echo "</div>";
		// TODO this doesn't render the performance metrics etc below
		log_uncaught_exception(new GraphException("Unknown graph type " . htmlspecialchars($graph['graph_type'])));
		return;
	}
	$graph_type = $graph_types[$graph['graph_type']];

	// check that if this graph is only for admins, then we are an admin
	if (isset($graph_type['admin']) && $graph_type['admin'] && !is_admin()) {
		throw new GraphException("Access denied to admin-only graph");
	}

	// get relevant technicals, if any
	// (we need to get these before render_graph_controls() so that the edit graph inline form knows about technicals)
	$graph['technicals'] = load_technicals($graph, $is_public);

	if (isset($graph['arg0_resolved']) && substr($graph['graph_type'], 0, strlen("securities_")) == "securities_") {
		// we need to unresolve the name to find the appropriate security
		$split = explode("_", $graph['graph_type'], 3);
		$securities = get_security_exchange_pairs();
		$tables = get_security_exchange_tables();
		if (count($securities[$split[1]]) > 1) {
			$q = db()->prepare("SELECT * FROM " . $tables[$split[1]] . " WHERE name=? AND currency=?");
			$q->execute(array($graph['arg0_resolved'], $split[2]));
		} else {
			$q = db()->prepare("SELECT * FROM  " . $tables[$split[1]] . " WHERE name=?");
			$q->execute(array($graph['arg0_resolved']));
		}
		$sec = $q->fetch();
		if ($sec) {
			$graph['arg0'] = $sec['id'];
		}
	}

	// obtain the heading from a callback?
	// TODO should refactor this with historical.php
	if (isset($graph_type['arg0']) && isset($graph['arg0'])) {
		$new_titles = $graph_type['arg0'](isset($graph_type['param0']) ? $graph_type['param0'] : false, isset($graph_type['param1']) ? $graph_type['param1'] : false);
		if (!isset($new_titles[$graph['arg0']])) {
			// has arg0 been set?
			$graph_type['heading'] = "(Unknown: " . htmlspecialchars($graph['arg0']) . ")";
		} else {
			$graph_type['heading'] = $new_titles[$graph['arg0']];
		}
	}

	// rewrite the heading with a title_callback?
	if (isset($graph_type['category'])) {
		throw new GraphException("Cannot render a category");
	}
	if (!isset($graph_type['heading'])) {
		// should be caught by tests
		throw new GraphException("Graph '" . htmlspecialchars($graph['graph_type']) . "' does not have a heading");
	}
	$graph_type['heading_key'] = $graph_type['heading'];
	if (isset($graph_type['title_callback']) && $graph_type['title_callback']) {
		$graph_type['heading'] = $graph_type['title_callback']($graph['graph_type'], $graph_type['heading']);
	}

	// does it have a link to historical data?
	$historical = false;
	if (isset($graph_type['historical']) && $graph_type['historical']) {
		if (isset($graph_type['historical_arg0'])) {
			$historical = $graph_type['historical']($graph_type['historical_arg0'], $graph_type, $graph, isset($graph_type['historical_param0']) ? $graph_type['historical_param0'] : false, isset($graph_type['historical_param1']) ? $graph_type['historical_param1'] : false);
		} else {
			$historical = $graph_type['historical']($graph_type, $graph, isset($graph_type['historical_param0']) ? $graph_type['historical_param0'] : false, isset($graph_type['historical_param1']) ? $graph_type['historical_param1'] : false);
		}
	}

	echo "<div class=\"graph_headings\">\n";
	echo "<h2 class=\"graph_title\">\n";
	if ($historical) echo "<a href=\"" . htmlspecialchars($historical) . "\" title=\"View historical data\">";
	echo htmlspecialchars(isset($graph_type['heading']) ? $graph_type['heading'] : $graph_type['title']);
	if ($historical) echo "</a>";
	echo "</h2>\n";
	echo "<span class=\"subheading\" id=\"subheading_" . htmlspecialchars($graph['id']) . "\"></span>\n";
	echo "<span class=\"last_updated\" id=\"last_updated_" . htmlspecialchars($graph['id']) . "\"></span>\n";		// issue #46: Chrome rendering bugs mean we need to render last_updated as per subheadings
	render_graph_controls($graph);
	echo "</div>\n";

	// don't render anything if we're rendering linebreak or heading - we don't need a callback here
	if (!($graph['graph_type'] == 'heading' || $graph['graph_type'] == 'linebreak')) {

		// we'll use ajax to render the graph
		$args = array();
		if (require_get('demo', false)) {
			$args['demo'] = require_get('demo');
		}
		if (require_get('debug_show_missing_data', false)) {
			$args['debug_show_missing_data'] = require_get('debug_show_missing_data');
		}
		if ($is_public) {
			$args += array(
				'graph_type' => $graph['graph_type'],
				'days' => isset($graph['days']) ? $graph['days'] : null,
				'height' => $graph['height'],
				'width' => $graph['width'],
				'delta' => $graph['delta'],
				'arg0' => isset($graph['arg0']) ? $graph['arg0'] : null,
				'arg0_resolved' => isset($graph['arg0_resolved']) ? $graph['arg0_resolved'] : null,
				'id' => isset($graph['id']) ? $graph['id'] : null,
				'no_technicals' => isset($graph['no_technicals']) ? $graph['no_technicals'] : null,
			);
			$ajax_url = url_for('graph_public', $args);
		} else {
			$args += array(
				'id' => $graph['id'],
			);
			$ajax_url = url_for('graph', $args);
		}

		$user = user_logged_in() ? get_user(user_id()) : false;
		if ($user) {
			if ($user['disable_graph_refresh'] || (isset($graph_type['no_refresh']) && $graph_type['no_refresh'])) {
				$timeout = 0;	// disable refresh
			} else {
				$timeout = get_premium_value(get_user(user_id()), 'graph_refresh');
			}
		} else {
			$timeout = get_site_config('graph_refresh_public');
		}

		?>
		<script type="text/javascript">
		google.load("visualization", "1", {packages:["corechart"]});
		function callbackGraph<?php echo htmlspecialchars($graph['id']); ?>() {
			queue_ajax_request(<?php echo json_encode($ajax_url); ?>, {
				'success': function(data, text, xhr) {
					$("#ajax_graph_target_<?php echo htmlspecialchars($graph['id']); ?>").html(data);
					<?php if ($timeout > 0) { ?>
					setTimeout(callbackGraph<?php echo htmlspecialchars($graph['id']); ?>, <?php echo $timeout * 1000 * 60; ?>);
					<?php } ?>
				},
				'error': function(xhr, text, error) {
					$("#ajax_graph_target_<?php echo htmlspecialchars($graph['id']); ?>").html(xhr.responseText);
					<?php if ($timeout > 0) { ?>
					setTimeout(callbackGraph<?php echo htmlspecialchars($graph['id']); ?>, <?php echo $timeout * 1000 * 60; ?>);
					<?php } ?>
				}
			})
		}
		$(document).ready(callbackGraph<?php echo htmlspecialchars($graph['id']); ?>);

		<?php if ($timeout > 0) { /* get better analytics, since graphs now update themselves */ ?>
		$(document).ready(function() {
			if (typeof track_graphs == 'undefined') {
				track_graphs = function() {
					if (typeof _gaq != 'undefined') {
						_gaq.push(['_trackEvent', 'Graphs', 'Idle', /* optional label */]);
					}
				};
				setInterval(track_graphs, <?php echo $timeout * 1000 * 60; ?>);
				track_graphs();
			}
		});
		<?php } ?>
		</script>
		<div id="ajax_graph_target_<?php echo htmlspecialchars($graph['id']); ?>"<?php echo get_dimensions($graph); ?>><span class="status_loading">Loading...</span></div>
		<?php

	}

	echo "</div>\n";	// end graph div

}

function render_graph_actual($graph, $is_public) {

	if (is_admin() && !require_get("demo", false)) {
		$start_time = microtime(true);
	}

	$graph_types = $is_public ? graph_types_public() : graph_types();
	if (!isset($graph_types[$graph['graph_type']])) {
		// this should never happen because graph_types should be checked before load_technicals is called
		throw new Exception("Could not render graph '" . htmlspecialchars($graph['graph_type']) . "': no such graph type");
	}

	// check that if this graph is only for admins, then we are an admin
	if (isset($graph_type['admin']) && $graph_type['admin'] && !is_admin()) {
		throw new GraphException("Access denied to admin-only graph");
	}

	// get relevant technicals, if any
	// (we need to get these before render_graph_controls() so that the edit graph inline form knows about technicals)
	$graph['technicals'] = load_technicals($graph, $is_public);

	$add_more_currencies = "<a href=\"" . htmlspecialchars(url_for('wizard_currencies')) . "\">Add more currencies</a>";

	switch ($graph['graph_type']) {

		case "btc_equivalent":
			// a pie chart

			// get all balances
			$balances = get_all_summary_instances();
			$graph['last_updated'] = find_latest_created_at($balances, "total");

			// and convert them using the most recent rates
			$rates = get_all_recent_rates();

			// create data
			$data = array();
			if (isset($balances['totalbtc']) && $balances['totalbtc']['balance'] != 0) {
				$data['BTC'] = graph_number_format(demo_scale($balances['totalbtc']['balance']));
			}
			foreach (get_all_currencies() as $cur) {
				if ($cur == 'btc') continue;
				if (!is_fiat_currency($cur) && isset($balances['total' . $cur]) && $balances['total' . $cur]['balance'] != 0 && isset($rates['btc' . $cur])) {
					$data[get_currency_abbr($cur)] = graph_number_format(demo_scale($balances['total' . $cur]['balance'] * $rates['btc' . $cur]['bid']));
				}
				if (is_fiat_currency($cur) && isset($balances['total' . $cur]) && $balances['total' . $cur]['balance'] != 0 && isset($rates[$cur . 'btc']) && $rates[$cur . 'btc'] /* no div by 0 */) {
					$data[get_currency_abbr($cur)] = graph_number_format(demo_scale($balances['total' . $cur]['balance'] / $rates[$cur . 'btc']['ask']));
				}
			}

			// calculate total for subheading
			$total = 0;
			foreach ($data as $currency => $value) {
				$total += $value;
			}
			$graph['subheading'] = number_format_html($total, 4);

			// sort data by balance
			arsort($data);

			// create headings with colours
			$headings = array();
			foreach ($data as $key => $value) {
				$headings[get_currency_abbr($key)] = array('color' => default_chart_color(array_search(strtolower(substr($key, 0, 3)), get_all_currencies())));
			}
			$data[0] = $headings;

			if ($data) {
				render_pie_chart($graph, $data, 'Currency', 'BTC');
			} else {
				render_text($graph, "Either you have not specified any accounts or addresses, or these addresses and accounts have not yet been updated.
					<br><a href=\"" . htmlspecialchars(url_for('wizard_accounts')) . "\">Add accounts and addresses</a>");
			}
			break;

		case "btc_equivalent_graph":
			// pass it off to the graph helper
			render_balances_btc_equivalent_graph($graph, user_id());
			break;

		case "btc_equivalent_stacked":
			// pass it off to the graph helper
			render_balances_btc_equivalent_graph($graph, user_id(), true /* stacked */);
			break;

		case "btc_equivalent_proportional":
			// pass it off to the graph helper
			render_balances_btc_equivalent_graph($graph, user_id(), true /* stacked */, true /* proprtional */);
			break;

		case "mtgox_btc_table":
			// a table of just BTC/USD rates
			$rates = get_all_recent_rates();
			$graph['last_updated'] = find_latest_created_at($rates['usdbtc']);

			$data = array(
				array('Bid', currency_format('usd', $rates['usdbtc']['bid'], 4)),
				array('Ask', currency_format('usd', $rates['usdbtc']['ask'], 4)),
			);

			render_table_vertical($graph, $data);
			break;

		case "balances_table":
			// a table of each currency
			// get all balances
			$balances = get_all_summary_instances();
			$summaries = get_all_summary_currencies();
			$currencies = get_all_currencies();
			$graph['last_updated'] = find_latest_created_at($balances, "total");

			// create data
			$data = array();
			foreach ($currencies as $c) {
				if (isset($summaries[$c])) {
					$balance = isset($balances['total'.$c]) ? $balances['total'.$c]['balance'] : 0;
					$data[] = array(get_currency_abbr($c), currency_format($c, demo_scale($balance), 4));
				}
			}

			$graph["extra"] = $add_more_currencies;
			render_table_vertical($graph, $data);
			break;

		case "total_converted_table":
			// a table of each all2fiat value
			// get all balances
			$currencies = get_total_conversion_summary_types();
			$graph['last_updated'] = 0;

			// create data
			$data = array();
			foreach ($currencies as $key => $c) {
				$q = db()->prepare("SELECT * FROM summary_instances WHERE user_id=? AND summary_type=? AND is_recent=1");
				$q->execute(array(user_id(), "all2".$key));
				if ($balance = $q->fetch()) {
					$data[] = array($c['short_title'], currency_format($c['currency'], demo_scale($balance['balance']), 4));
					$graph['last_updated'] = max($graph['last_updated'], strtotime($balance['created_at']));
				}
			}

			$graph["extra"] = $add_more_currencies;
			render_table_vertical($graph, $data);
			break;

		case "crypto_converted_table":
			// a table of each crypto2xxx value
			// get all balances
			$currencies = get_crypto_conversion_summary_types();
			$graph['last_updated'] = 0;

			// create data
			$data = array();
			foreach ($currencies as $key => $c) {
				$q = db()->prepare("SELECT * FROM summary_instances WHERE user_id=? AND summary_type=? AND is_recent=1");
				$q->execute(array(user_id(), "crypto2".$key));
				if ($balance = $q->fetch()) {
					$data[] = array($c['short_title'], currency_format($c['currency'], demo_scale($balance['balance']), 4));
					$graph['last_updated'] = max($graph['last_updated'], strtotime($balance['created_at']));
				}
			}

			$graph["extra"] = $add_more_currencies;
			render_table_vertical($graph, $data);
			break;

		case "balances_offset_table":
			// a table of each currency, along with an offset field
			$balances = get_all_summary_instances();
			$summaries = get_all_summary_currencies();
			$offsets = get_all_offset_instances();
			$currencies = get_all_currencies();
			$graph['last_updated'] = find_latest_created_at($balances, "total");

			// create data
			$data = array();
			$data[] = array('Currency', 'Balance', 'Offset', 'Total');
			foreach ($currencies as $c) {
				if (isset($summaries[$c])) {
					$balance = demo_scale(isset($balances['total'.$c]) ? $balances['total'.$c]['balance'] : 0);
					$offset = demo_scale(isset($offsets[$c]) ? $offsets[$c]['balance'] : 0);
					$data[] = array(
						get_currency_abbr($c),
						currency_format($c, $balance - $offset, 4),
						// HTML quirk: "When there is only one single-line text input field in a form, the user agent should accept Enter in that field as a request to submit the form."
						'<form action="' . htmlspecialchars(url_for('set_offset', array('page' => $graph['page_id']))) . '" method="post">' .
							'<input type="text" name="' . htmlspecialchars($c) . '" value="' . htmlspecialchars($offset == 0 ? '' : number_format_autoprecision($offset)) . '" maxlength="32">' .
							'</form>',
						currency_format($c, $balance /* balance includes offset */, 4),
					);
				}
			}

			$graph['extra'] = $add_more_currencies;

			render_table_horizontal_vertical($graph, $data);
			break;

		case "ticker_matrix":
			// a matrix table of each currency vs. each currency, and their current
			// last_trade and volume on each exchange the user is interested in
			$currencies = get_all_currencies();
			$summaries = get_all_summary_currencies();
			$conversion = get_all_conversion_currencies();

			$graph["last_updated"] = 0;
			$header = array("");
			$interested = array();
			foreach ($currencies as $c) {
				if (isset($summaries[$c])) {
					$header[] = get_currency_abbr($c);
					$interested[] = $c;
				}
			}
			$data[] = $header;

			foreach ($interested as $c1) {
				$row = array(get_currency_abbr($c1));
				foreach ($interested as $c2) {
					// go through each exchange pair
					$cell = "";
					foreach (get_exchange_pairs() as $exchange => $pairs) {
						foreach ($pairs as $pair) {
							if ($c1 == $pair[0] && $c2 == $pair[1]) {
								$q = db()->prepare("SELECT * FROM ticker_recent WHERE exchange=? AND currency1=? AND currency2=? LIMIT 1");
								$q->execute(array($exchange, $c1, $c2));
								if ($ticker = $q->fetch()) {
									// TODO currency_format should be a graph option
									$exchange_short = strlen($exchange) > 8 ? substr($exchange, 0, 7) . "..." : $exchange;
									$cell .= "<li><span class=\"rate\">" . number_format_html($ticker['last_trade'], 4) . "</span> " . ($ticker['volume'] == 0 ? "" : "<span class=\"volume\">(" . number_format_html($ticker['volume'], 4) . ")</span>");
									$cell .= " <span class=\"exchange\" title=\"" . htmlspecialchars(get_exchange_name($exchange)) . "\">[" . htmlspecialchars($exchange_short) . "]</span>";
									$cell .= "</li>\n";
									$graph['last_updated'] = max($graph['last_updated'], strtotime($ticker['created_at']));
								} else {
									$cell .= "<li class=\"warning\">Could not find rate for " . htmlspecialchars($exchange . ": " . $c1 . "/" . $c2) . "</li>\n";
								}
							}
						}
					}
					if ($cell) {
						$cell = "<ul class=\"rate_matrix\">" . $cell . "</ul>";
					}
					$row[] = $cell;
				}
				$data[] = $row;
			}

			// now delete any empty rows or columns
			// columns
			$deleteRows = array();
			$deleteColumns = array();
			for ($i = 1; $i < count($data); $i++) {
				$empty = true;
				for ($j = 1; $j < count($data[$i]); $j++) {
					if ($data[$i][$j]) {
						$empty = false;
						break;
					}
				}
				if ($empty) $deleteRows[] = $i;
			}
			for ($i = 1; $i < count($data); $i++) {
				$empty = true;
				for ($j = 1; $j < count($data[$i]); $j++) {
					if ($data[$j][$i]) {
						$empty = false;
						break;
					}
				}
				if ($empty) $deleteColumns[] = $i;
			}

			$new_data = array();
			foreach ($data as $i => $row) {
				if (in_array($i, $deleteRows)) continue;
				$x = array();
				foreach ($data[$i] as $j => $cell) {
					if (in_array($j, $deleteColumns)) continue;
					$x[] = $cell;
				}
				$new_data[] = $x;
			}

			$graph['extra'] = $add_more_currencies;
			render_table_horizontal_vertical($graph, $new_data);

			break;

		case "external_historical":
			render_external_graph($graph);
			break;

		case "statistics_queue":
			render_site_statistics_queue($graph);
			break;

		case "admin_statistics":
			render_site_admin_statistics($graph);
			break;

		case "statistics_system_load":
			render_site_statistics_system_load($graph, "");
			break;

		case "statistics_db_system_load":
			render_site_statistics_system_load($graph, "db_");
			break;

		case "metrics_db_slow_queries":
			render_metrics_db_slow_queries($graph);
			break;

		case "metrics_db_slow_queries_graph":
			render_metrics_db_slow_queries_graph($graph);
			break;

		case "metrics_curl_slow_urls":
			render_metrics_curl_slow_urls($graph);
			break;

		case "metrics_curl_slow_urls_graph":
			render_metrics_curl_slow_urls_graph($graph);
			break;

		case "metrics_slow_jobs_graph":
			render_metrics_slow_jobs_graph($graph);
			break;

		case "metrics_slow_jobs_database_graph":
			render_metrics_slow_jobs_database_graph($graph);
			break;

		case "metrics_slow_pages_graph":
			render_metrics_slow_pages_graph($graph);
			break;

		case "metrics_slow_pages_database_graph":
			render_metrics_slow_pages_database_graph($graph);
			break;

		case "metrics_slow_graphs_graph":
			render_metrics_slow_graphs_graph($graph);
			break;

		case "metrics_slow_graphs_database_graph":
			render_metrics_slow_graphs_database_graph($graph);
			break;

		case "metrics_slow_graphs_count_graph":
			render_metrics_slow_graphs_count_graph($graph);
			break;

		case "metrics_jobs_frequency_graph":
			render_metrics_jobs_frequency_graph($graph);
			break;

		case "calculator":
			require(__DIR__ . "/../site/_calculator.php");
			?>
			<script type="text/javascript">
			$(document).ready(function() {
				initialise_calculator($("#graph<?php echo htmlspecialchars($graph['id']); ?>"))
			});
			</script>
			<?php
			break;

		case "linebreak":
			// implemented by profile.php
			break;

		case "heading":
			// implemented by profile.php
			break;

		default:
			// ticker graphs are generated programatically
			if (substr($graph['graph_type'], -strlen("_daily")) == "_daily") {
				$split = explode("_", $graph['graph_type']);
				if (count($split) == 3 && strlen($split[1]) == 6) {
					// checks
					$exchange_pairs = get_exchange_pairs();
					if (isset($exchange_pairs[$split[0]])) {
						// we won't check pairs, we just won't get any data
						render_ticker_graph($graph, $split[0], substr($split[1], 0, 3), substr($split[1], 3));
						break;
					}
				}

				// total summary graphs are generated programatically
				if (substr($graph['graph_type'], 0, strlen("total_")) == "total_") {
					$split = explode("_", $graph['graph_type']);
					if (count($split) == 3 && strlen($split[1]) == 3) {
						// we will assume that it is the current user
						// (otherwise it might be possible to view other users' data)
						render_summary_graph($graph, 'total' . $split[1], $split[1], user_id());
						break;
					}
				}

				// total hashrate graphs are generated programatically
				if (substr($graph['graph_type'], 0, strlen("hashrate_")) == "hashrate_") {
					$split = explode("_", $graph['graph_type']);
					if (count($split) == 3 && strlen($split[1]) == 3) {
						// we will assume that it is the current user
						// (otherwise it might be possible to view other users' data)
						render_summary_graph($graph, 'totalmh_' . $split[1], $split[1], user_id(), "MH/s");
						break;
					}
				}

				$summary_types = array('all2', 'crypto2');
				$was_summary_type = false;
				foreach ($summary_types as $st) {
					if (substr($graph['graph_type'], 0, strlen($st)) == $st) {
						$split = explode("_", $graph['graph_type']);
						if (count($split) == 2 && strlen($split[0]) == strlen($st) + 3) {
							$cur = substr($split[0], strlen($st));
							// e.g. all2nzd

							// we will assume that it is the current user
							// (otherwise it might be possible to view other users' data)
							render_summary_graph($graph, $st . $cur, $cur, user_id());
							$was_summary_type = true;
							break;
						} else if (count($split) == 3 && strlen($split[0]) == strlen($st) + 3) {
							$cur = substr($split[0], strlen($st));
							// e.g. all2usd_mtgox

							// we will assume that it is the current user
							// (otherwise it might be possible to view other users' data)
							render_summary_graph($graph, $st . $cur . '_' . $split[1], $cur, user_id());
							$was_summary_type = true;
							break;
						}
					}
				}
				if ($was_summary_type) break;

			}

			// composition charts
			if (substr($graph['graph_type'], 0, strlen("composition_")) == "composition_") {
				$split = explode("_", $graph['graph_type']);
				if (count($split) == 3 && strlen($split[1]) == 3 && in_array($split[1], get_all_currencies())) {
					$currency = $split[1];

					switch ($split[2]) {
						case "pie":
						case "table":
							// get data
							// TODO could probably cache this
							$q = db()->prepare("SELECT SUM(balance) AS balance, exchange, MAX(created_at) AS created_at FROM balances WHERE user_id=? AND is_recent=1 AND currency=? GROUP BY exchange");
							$q->execute(array(user_id(), $currency));
							$balances = $q->fetchAll();

							// need to also get address balances
							$summary_balances = get_all_summary_instances();

							// create data
							$data = array();
							if (isset($summary_balances['blockchain' . $currency]) && $summary_balances['blockchain' . $currency]['balance'] != 0) {
								$balances[] = array(
									"balance" => $summary_balances['blockchain' . $currency]['balance'],
									"exchange" => "blockchain",
									"created_at" => $summary_balances['blockchain' . $currency]['created_at'],
								);
							}
							if (isset($summary_balances['offsets' . $currency]) && $summary_balances['offsets' . $currency]['balance'] != 0) {
								$balances[] = array(
									"balance" => $summary_balances['offsets' . $currency]['balance'],
									"exchange" => "offsets",
									"created_at" => $summary_balances['offsets' . $currency]['created_at'],
								);
							}

							$graph['last_updated'] = find_latest_created_at($balances);

							// apply demo_scale and calculate total summary
							$data = array();
							$total = 0;
							foreach ($balances as $b) {
								if ($b['balance'] != 0) {
									$data[get_exchange_name($b['exchange'])] = demo_scale($b['balance']);
									$total += $data[get_exchange_name($b['exchange'])];
								}
							}
							$graph['subheading'] = number_format_html($total, 4);

							// sort data by balance
							arsort($data);

							if ($data) {
								if ($split[2] == "pie") {
									render_pie_chart($graph, $data, 'Source', get_currency_abbr($currency));
								} else {
									$table = array();
									$sum = 0;
									foreach ($data as $exchange_name => $exchange_data) {
										$table[] = array($exchange_name, currency_format($currency, $exchange_data, 4));
										$sum += $exchange_data;
									}
									$head = array(
										array("Total " . get_currency_abbr($currency), currency_format($currency, $sum, 4)),
									);
									render_table_vertical($graph, $table, $head);
								}
							} else {
								render_text($graph, "Either you have not specified any accounts or addresses in " . get_currency_name($currency) . ", or these addresses and accounts have not yet been updated.
									<br><a href=\"" . htmlspecialchars(url_for('wizard_accounts')) . "\">Add accounts and addresses</a>");
							}
							break 2;

						case "daily":
							// pass it off to the graph helper
							render_balances_composition_graph($graph, $currency, user_id());
							break 2;

						case "stacked":
							// pass it off to the graph helper
							render_balances_composition_graph($graph, $currency, user_id(), true /* stacked */);
							break 2;

						case "proportional":
							// pass it off to the graph helper
							render_balances_composition_graph($graph, $currency, user_id(), true /* stacked */, true /* proportional */);
							break 2;

					}

				}
			}

			// securities charts
			if (substr($graph['graph_type'], 0, strlen("securities_")) == "securities_" && isset($graph['arg0'])) {
				$securities = get_security_exchange_pairs();
				$split = explode("_", $graph['graph_type'], 3);
				if (in_array($split[2], get_all_currencies()) && isset($securities[$split[1]])) {
					render_balances_graph($graph, 'securities_' . $split[1], $split[2], get_site_config('system_user_id'), $graph['arg0']);
					break;
				} else {
					render_text($graph, "Couldn't render securities graph " . htmlspecialchars($graph['graph_type']));
					log_uncaught_exception(new GraphException("Couldn't render securities graph " . htmlspecialchars($graph['graph_type'])));
					break;
				}
			}

			// let's not throw an exception, let's just render an error message
			render_text($graph, "Couldn't render graph type " . htmlspecialchars($graph['graph_type']));
			log_uncaught_exception(new GraphException("Couldn't render graph type " . htmlspecialchars($graph['graph_type'])));
			break;
	}

	if (is_admin() && !require_get("demo", false)) {
		$end_time = microtime(true);
		$time_diff = ($end_time - $start_time) * 1000;
		echo "<div style=\"position: relative; width: 100%; height: 0;\"><span style=\"position: absolute; text-align: right; width: 100%; height: 1em; overflow: hidden;\" class=\"render_time\">" . htmlspecialchars($graph['graph_type']) . " " . number_format($time_diff, 2) . " ms" . (get_site_config('timed_sql') ? ": " . db()->stats() : "") . ", order " . number_format($graph['page_order']) . "</span></div>";
	}

	performance_metrics_graph_complete($graph);

}
