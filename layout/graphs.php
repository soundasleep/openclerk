<?php

require("graphs/util.php");
require("graphs/types.php");
require("graphs/render.php");
require("graphs/output.php");

/**
 * Renders a particular graph.
 */
function render_graph($graph, $is_public = false) {

	if (is_admin()) {
		$start_time = microtime(true);
	}

	$graph_types = $is_public ? graph_types_public() : graph_types();
	if (!isset($graph_types[$graph['graph_type']])) {
		// let's not crash with an exception, let's just display an error
		render_graph_controls($graph);
		render_text($graph, "Unknown graph type " . htmlspecialchars($graph['graph_type']));
		log_uncaught_exception(new GraphException("Unknown graph type " . htmlspecialchars($graph['graph_type'])));
		return;
	}
	$graph_type = $graph_types[$graph['graph_type']];

	// get relevant technicals, if any
	// (we need to get these before render_graph_controls() so that the edit graph inline form knows about technicals)
	if (isset($graph_types[$graph['graph_type']]['technical']) && $graph_types[$graph['graph_type']]['technical']) {
		$q = db()->prepare("SELECT * FROM graph_technicals WHERE graph_id=?");
		$q->execute(array($graph['id']));
		$graph['technicals'] = $q->fetchAll();
	}

	if (isset($graph['arg0_resolved']) && substr($graph['graph_type'], 0, strlen("securities_")) == "securities_") {
		// we need to unresolve the name to find the appropriate security
		$split = explode("_", $graph['graph_type'], 3);
		$securities = get_security_exchange_pairs();
		if (count($securities[$split[1]]) > 1) {
			$q = db()->prepare("SELECT * FROM securities_" . $split[1] . " WHERE name=? AND currency=?");
			$q->execute(array($graph['arg0_resolved'], $split[2]));
		} else {
			$q = db()->prepare("SELECT * FROM securities_" . $split[1] . " WHERE name=?");
			$q->execute(array($graph['arg0_resolved']));
		}
		$sec = $q->fetch();
		if ($sec) {
			$graph['arg0'] = $sec['id'];
		}
	}

	// obtain the heading from a callback?
	if (isset($graph_type['arg0']) && isset($graph['arg0'])) {
		$new_titles = $graph_type['arg0']();
		if (!isset($new_titles[$graph['arg0']])) {
			// has arg0 been set?
			$graph_type['heading'] = "(Unknown: " . htmlspecialchars($graph['arg0']) . ")";
		} else {
			$graph_type['heading'] = $new_titles[$graph['arg0']];
		}
	}

	// does it have a link to historical data?
	$historical = false;
	if (isset($graph_type['historical']) && $graph_type['historical']) {
		if (isset($graph_type['historical_arg0'])) {
			$historical = $graph_type['historical']($graph_type['historical_arg0'], $graph_type, $graph);
		} else {
			$historical = $graph_type['historical']($graph_type, $graph);
		}
	}

	echo "<h2 class=\"graph_title\">";
	if ($historical) echo "<a href=\"" . htmlspecialchars($historical) . "\" title=\"View historical data\">";
	echo htmlspecialchars(isset($graph_type['heading']) ? $graph_type['heading'] : $graph_type['title']);
	if ($historical) echo "</a>";
	echo "</h2>\n";
	render_graph_controls($graph);

	$add_more_currencies = "<a href=\"" . htmlspecialchars(url_for('user')) . "\">Add more currencies</a>";

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
			if (isset($balances['totalltc']) && $balances['totalltc']['balance'] != 0 && isset($rates['btcltc'])) {
				$data['LTC'] = graph_number_format(demo_scale($balances['totalltc']['balance'] * $rates['btcltc']['sell']));
			}
			if (isset($balances['totalnmc']) && $balances['totalnmc']['balance'] != 0 && isset($rates['btcnmc'])) {
				$data['NMC'] = graph_number_format(demo_scale($balances['totalnmc']['balance'] * $rates['btcnmc']['sell']));
			}
			if (isset($balances['totalftc']) && $balances['totalftc']['balance'] != 0 && isset($rates['btcftc'])) {
				$data['FTC'] = graph_number_format(demo_scale($balances['totalftc']['balance'] * $rates['btcftc']['sell']));
			}
			if (isset($balances['totalppc']) && $balances['totalppc']['balance'] != 0 && isset($rates['btcftc'])) {
				$data['PPC'] = graph_number_format(demo_scale($balances['totalppc']['balance'] * $rates['btcppc']['sell']));
			}
			if (isset($balances['totalusd']) && $balances['totalusd']['balance'] != 0 && isset($rates['usdbtc']) && $rates['usdbtc'] /* no div by 0 */) {
				$data['USD'] = graph_number_format(demo_scale($balances['totalusd']['balance'] / $rates['usdbtc']['buy']));
			}
			if (isset($balances['totalnzd']) && $balances['totalnzd']['balance'] != 0 && isset($rates['nzdbtc']) && $rates['nzdbtc'] /* no div by 0 */) {
				$data['NZD'] = graph_number_format(demo_scale($balances['totalnzd']['balance'] / $rates['nzdbtc']['buy']));
			}

			// sort data by balance
			arsort($data);

			if ($data) {
				render_pie_chart($graph, $data, 'Currency', 'BTC');
			} else {
				render_text($graph, "Either you have not specified any accounts or addresses, or these addresses and accounts have not yet been updated.
					<br><a href=\"" . htmlspecialchars(url_for('accounts')) . "\">Add accounts and addresses</a>");
			}
			break;

		case "mtgox_btc_table":
			// a table of just BTC/USD rates
			$rates = get_all_recent_rates();
			$graph['last_updated'] = find_latest_created_at($rates['usdbtc']);

			$data = array(
				array('Buy', currency_format('usd', $rates['usdbtc']['buy'], 4)),
				array('Sell', currency_format('usd', $rates['usdbtc']['sell'], 4)),
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
					$data[] = array(strtoupper($c), currency_format($c, demo_scale($balance), 4));
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
						strtoupper($c),
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
					$header[] = strtoupper($c);
					$interested[] = $c;
				}
			}
			$data[] = $header;

			foreach ($interested as $c1) {
				$row = array(strtoupper($c1));
				foreach ($interested as $c2) {
					// go through each exchange pair
					$cell = "";
					foreach (get_exchange_pairs() as $exchange => $pairs) {
						foreach ($pairs as $pair) {
							if ($c1 == $pair[0] && $c2 == $pair[1]) {
								$q = db()->prepare("SELECT * FROM ticker WHERE exchange=? AND currency1=? AND currency2=? AND is_recent=1 LIMIT 1");
								$q->execute(array($exchange, $c1, $c2));
								if ($ticker = $q->fetch()) {
									// TODO currency_format should be a graph option
									$cell .= "<li><span class=\"rate\">" . number_format_html($ticker['last_trade'], 4) . "</span> " . ($ticker['volume'] == 0 ? "" : "<span class=\"volume\">(" . number_format_html($ticker['volume'], 4) . ")</span>") . " <span class=\"exchange\">[" . htmlspecialchars($exchange) . "]</span></li>\n";
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

							$data = array();
							foreach ($balances as $b) {
								if ($b['balance'] != 0) {
									$data[get_exchange_name($b['exchange'])] = demo_scale($b['balance']);
								}
							}

							// sort data by balance
							arsort($data);

							if ($data) {
								render_pie_chart($graph, $data, 'Source', strtoupper($currency));
							} else {
								render_text($graph, "Either you have not specified any accounts or addresses in " . get_currency_name($currency) . ", or these addresses and accounts have not yet been updated.
									<br><a href=\"" . htmlspecialchars(url_for('accounts')) . "\">Add accounts and addresses</a>");
							}
							break 2;

						case "daily":
							// pass it off to the graph helper
							render_balances_composition_graph($graph, $currency, user_id());
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
				}
			}

			// let's not throw an exception, let's just render an error message
			render_text($graph, "Couldn't render graph type " . htmlspecialchars($graph['graph_type']));
			log_uncaught_exception(new GraphException("Couldn't render graph type " . htmlspecialchars($graph['graph_type'])));
			break;
	}

	if (is_admin()) {
		$end_time = microtime(true);
		$time_diff = ($end_time - $start_time) * 1000;
		echo "<span class=\"render_time\">" . number_format($time_diff, 2) . " ms" . (get_site_config('timed_sql') ? ": " . db()->stats() : "") . ", order " . number_format($graph['page_order']) . "</span>";
	}

}
