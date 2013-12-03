<?php


function get_graph_days($graph) {
	return ((isset($graph['days']) && $graph['days'] > 0) ? ((int) $graph['days']) : 45);
}

// e.g. a SMA with a period of 10 requires the 10 days of data before as well
function extra_days_necessary($graph) {
	$count = 0;
	if (isset($graph['technicals'])) {
		foreach ($graph['technicals'] as $t) {
			$count = min(get_site_config('technical_period_max'), $t['technical_period']);
		}
	}
	return $count;
}

function calculate_technicals($graph, $data) {
	$days = get_graph_days($graph);
	$original_rows = count($data[0]);

	// need to sort data by date
	ksort($data);

	if (isset($graph['technicals'])) {
		$graph_technical_types = graph_technical_types();
		foreach ($graph['technicals'] as $t) {
			$i = -1;
			foreach ($data as $label => $row) {
				$i++;
				if ($i == 0) continue;	// skip heading row
				if ($i < count($data) - $days - 1) continue;	// skip period data that isn't displayed

				// we now actually calculate data
				switch ($t['technical_type']) {
					case "sma":
						// simple moving average
						$headings = array(array(
							'title' => "SMA (" . number_format($t['technical_period']) . ")",
							'line_width' => 1,
							'color' => default_chart_color(2),
							'technical' => true,
						));

						$last = 0;
						$sum = 0;
						for ($j = 0; $j < $t['technical_period']; $j++) {
							$key = date('Y-m-d', strtotime($label . " -$j days"));
							$last = isset($data[$key]) ?
								(isset($data[$key][2]) ? ($data[$key][1] + $data[$key][2]) / 2 : $data[$key][1]) : $last;	// 1 is 'buy', 2 is 'sell': take average if defined
							$sum += $last;
						}

						$data[$label][] = graph_number_format($sum / $t['technical_period']);
						break;

					default:
						if (isset($graph_technical_types[$t['technical_type']]['callback'])) {
							// a premium graph technical type, defined elsewhere
							// should return array('headings' => array, 'data' => array) for each row
							$result = $graph_technical_types[$t['technical_type']]['callback']($graph, $t, $label, $data);
							$headings = $result['headings'];
							foreach ($result['data'] as $value) {
								$data[$label][] = $value;
							}
							break;

						} else {
							throw new GraphException("Unknown technical type '" . $t['technical_type'] . "'");
						}
				}
			}

			// add headings
			foreach ($headings as $h) {
				$data[0][] = $h;
			}
		}
	}

	// move the first $original_rows to the end, so they are dislpayed on top
	if (count($data[0]) != $original_rows) {
		$data_new = array();
		foreach ($data as $label => $row) {
			$r = array();
			$r[] = $row[0];	// keep date row
			for ($j = $original_rows; $j < count($row); $j++) {
				$r[] = $row[$j];
			}
			for ($j = 1; $j < $original_rows; $j++) {
				$r[] = $row[$j];
			}
			$data_new[$label] = $r;
		}
		$data = $data_new;
	}

	return $data;
}

function render_ticker_graph($graph, $exchange, $cur1, $cur2) {

	$data = array();
	$data[0] = array("Date",
		array(
			'title' => strtoupper($cur1) . "/" . strtoupper($cur2) . " Buy",
			'line_width' => 2,
			'color' => default_chart_color(0),
		),
		array(
			'title' => strtoupper($cur1) . "/" . strtoupper($cur2) . " Sell",
			'line_width' => 2,
			'color' => default_chart_color(1),
		),
	);
	if ($exchange == 'themoneyconverter') {
		// hack fix because TheMoneyConverter only has last_trade
		unset($data[0][2]);
		$data[0][1]['title'] = strtoupper($cur1) . "/" . strtoupper($cur2);
	}
	$last_updated = false;
	$days = get_graph_days($graph);
	$extra_days = extra_days_necessary($graph);

	$sources = array(
		// cannot use 'LIMIT :limit'; PDO escapes :limit into string, MySQL cannot handle or cast string LIMITs
		// first get summarised data
		array('query' => "SELECT * FROM graph_data_ticker WHERE exchange=:exchange AND
			currency1=:currency1 AND currency2=:currency2 AND data_date > DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date'),
		// and then get more recent data
		array('query' => "SELECT * FROM ticker WHERE is_daily_data=1 AND exchange=:exchange AND
			currency1=:currency1 AND currency2=:currency2 ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at'),
	);

	$day_count = 0;
	foreach ($sources as $source) {
		$q = db()->prepare($source['query']); // TODO add days_to_display as parameter
		$q->execute(array(
			'exchange' => $exchange,
			'currency1' => $cur1,
			'currency2' => $cur2,
		));
		while ($ticker = $q->fetch()) {
			$data_key = date('Y-m-d', strtotime($ticker[$source['key']]));
			if ($exchange == 'themoneyconverter') {
				// hack fix because TheMoneyConverter only has last_trade
				$data[$data_key] = array(
					'new Date(' . date('Y, n-1, j', strtotime($ticker[$source['key']])) . ')',
					graph_number_format(/* last_trade is in ticker; last_trade_closing is in graph_data_ticker */ isset($ticker['last_trade']) ? $ticker['last_trade'] : $ticker['last_trade_closing']),
				);
			} else {
				$data[$data_key] = array(
					'new Date(' . date('Y, n-1, j', strtotime($ticker[$source['key']])) . ')',
					graph_number_format($ticker['buy']),
					graph_number_format($ticker['sell']),
				);
			}
			$last_updated = max($last_updated, strtotime($ticker['created_at']));
		}
	}

	// calculate technicals
	$data = calculate_technicals($graph, $data);

	// discard early data
	$data = discard_early_data($data, $days);

	// sort by key, but we only want values
	uksort($data, 'cmp_time');
	$graph['subheading'] = format_subheading_values($graph, $data);
	$graph['last_updated'] = $last_updated;
	render_linegraph_date($graph, array_values($data));
}

/**
 * Get the most recent data values, strip out any dates and technical indicator
 * values, and return a HTML string that can be used to show the most recent
 * data for this graph.
 */
function format_subheading_values($graph, $input, $suffix = false) {
	$array = array_slice($input, 1 /* skip heading row */, 1, true);
	$array = array_pop($array);	// array_slice returns an array(array(...))
	// array[0] is always the date; the remaining values are the formatted data
	// remove any data that is a Date heading or a technical value
	foreach ($input[0] as $key => $heading) {
		if ($key == 0 || (is_array($heading) && isset($heading['technical']) && $heading['technical'])) {
			unset($array[$key]);
		}
	}
	if (!$array) {
		return "";
	}
	foreach ($array as $key => $value) {
		$array[$key] = number_format_html($value, 4, $suffix);
	}
	return implode(" / ", $array);
}

function discard_early_data($data, $days) {
	$data_new = array();
	foreach ($data as $label => $row) {
		if ($label == 0 || strtotime($label) >= strtotime("-" . $days . " days -1 day")) {
			$data_new[$label] = $row;
		}
	}
	return $data_new;
}

function cmp_time($a, $b) {
	if ($a === 0) return -1;
	if ($b === 0) return 1;
	return strtotime($a) < strtotime($b);
}

function render_summary_graph($graph, $summary_type, $currency, $user_id, $row_title = false) {

	$data = array();
	$data[0] = array("Date",
		array(
			'title' => $row_title ? $row_title : strtoupper($currency),
			'line_width' => 2,
			'color' => default_chart_color(0),
		),
	);
	$last_updated = false;
	$days = get_graph_days($graph);
	$extra_days = extra_days_necessary($graph);

	$sources = array(
		// first get summarised data
		array('query' => "SELECT * FROM graph_data_summary WHERE user_id=:user_id AND summary_type=:summary_type AND
			data_date >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date', 'balance_key' => 'balance_closing'),
		// and then get more recent data
		array('query' => "SELECT * FROM summary_instances WHERE is_daily_data=1 AND summary_type=:summary_type AND
			user_id=:user_id ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at', 'balance_key' => 'balance'),
	);

	foreach ($sources as $source) {
		$q = db()->prepare($source['query']);
		$q->execute(array(
			'summary_type' => $summary_type,
			'user_id' => $user_id,
		));
		while ($ticker = $q->fetch()) {
			$data[date('Y-m-d', strtotime($ticker[$source['key']]))] = array(
				'new Date(' . date('Y, n-1, j', strtotime($ticker[$source['key']])) . ')',
				graph_number_format(demo_scale($ticker[$source['balance_key']])),
			);
			$last_updated = max($last_updated, strtotime($ticker['created_at']));
		}
	}

	// calculate technicals
	$data = calculate_technicals($graph, $data);

	// discard early data
	$data = discard_early_data($data, $days);

	// sort by key, but we only want values
	uksort($data, 'cmp_time');
	$graph['subheading'] = format_subheading_values($graph, $data);
	$graph['last_updated'] = $last_updated;

	if (count($data) > 1) {
		render_linegraph_date($graph, array_values($data));
	} else {
		render_text($graph, "Either you have not enabled this currency, or your summaries for this currency have not yet been updated.
					<br><a href=\"" . htmlspecialchars(url_for('wizard_currencies')) . "\">Configure currencies</a>");
	}

}

// TODO refactor with render_balances_composition_graph
function render_balances_graph($graph, $exchange, $currency, $user_id, $account_id) {

	$data = array();
	$data[0] = array("Date",
		array(
			'title' => strtoupper($currency),
			'line_width' => 2,
			'color' => default_chart_color(0),
		),
	);
	$last_updated = false;
	$days = get_graph_days($graph);
	$extra_days = extra_days_necessary($graph);

	$sources = array(
		// first get summarised data
		array('query' => "SELECT * FROM graph_data_balances WHERE user_id=:user_id AND exchange=:exchange AND account_id=:account_id AND currency=:currency AND
			data_date >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date', 'balance_key' => 'balance_closing'),
		// and then get more recent data
		array('query' => "SELECT * FROM balances WHERE is_daily_data=1 AND exchange=:exchange AND account_id=:account_id AND currency=:currency AND
			user_id=:user_id ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at', 'balance_key' => 'balance'),
	);

	foreach ($sources as $source) {
		$q = db()->prepare($source['query']);
		$q->execute(array(
			'exchange' => $exchange,
			'user_id' => $user_id,
			'account_id' => $account_id,
			'currency' => $currency,
		));
		while ($ticker = $q->fetch()) {
			$data[date('Y-m-d', strtotime($ticker[$source['key']]))] = array(
				'new Date(' . date('Y, n-1, j', strtotime($ticker[$source['key']])) . ')',
				graph_number_format(demo_scale($ticker[$source['balance_key']])),
			);
			$last_updated = max($last_updated, strtotime($ticker['created_at']));
		}
	}

	// calculate technicals
	$data = calculate_technicals($graph, $data);

	// discard early data
	$data = discard_early_data($data, $days);

	// sort by key, but we only want values
	uksort($data, 'cmp_time');
	$graph['subheading'] = format_subheading_values($graph, $data);
	$graph['last_updated'] = $last_updated;

	if (count($data) > 1) {
		render_linegraph_date($graph, array_values($data));
	} else {
		if ($user_id == get_site_config('system_user_id')) {
			render_text($graph, "Invalid balance type.");	// or there is no data to display
		} else {
			render_text($graph, "Either you have not enabled this balance, or your summaries for this balance have not yet been updated.
						<br><a href=\"" . htmlspecialchars(url_for('wizard_currencies')) . "\">Configure currencies</a>");
		}
	}

}

// TODO refactor with render_balances_graph
function render_balances_composition_graph($graph, $currency, $user_id) {

	$data = array();
	$last_updated = false;
	$days = get_graph_days($graph);
	$extra_days = extra_days_necessary($graph);
	$exchanges_found = array();
	$maximum_balances = array();	// only used to check for non-zero accounts

	$sources = array(
		// we can't LIMIT by days here, because we may have many accounts for one exchange
		// first get summarised data
		array('query' => "SELECT * FROM graph_data_balances WHERE user_id=:user_id AND currency=:currency
			AND data_date > DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days + 1) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date', 'balance_key' => 'balance_closing'),
		// and then get more recent data
		array('query' => "SELECT * FROM balances WHERE is_daily_data=1 AND currency=:currency
			AND user_id=:user_id AND created_at >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days + 1) . " DAY) ORDER BY created_at DESC", 'key' => 'created_at', 'balance_key' => 'balance'),
		// also include blockchain balances
		// first get summarised data
		array('query' => "SELECT *, 'blockchain' AS exchange FROM graph_data_summary WHERE user_id=:user_id AND summary_type=CONCAT('blockchain', :currency) AND
			data_date >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date', 'balance_key' => 'balance_closing'),
		// and then get more recent data
		array('query' => "SELECT *, 'blockchain' AS exchange FROM summary_instances WHERE is_daily_data=1 AND summary_type=CONCAT('blockchain', :currency) AND
			user_id=:user_id ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at', 'balance_key' => 'balance'),
		// also include offset balances
		// first get summarised data
		array('query' => "SELECT *, 'offsets' AS exchange FROM graph_data_summary WHERE user_id=:user_id AND summary_type=CONCAT('offsets', :currency) AND
			data_date >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date', 'balance_key' => 'balance_closing'),
		// and then get more recent data
		array('query' => "SELECT *, 'offsets' AS exchange FROM summary_instances WHERE is_daily_data=1 AND summary_type=CONCAT('offsets', :currency) AND
			user_id=:user_id ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at', 'balance_key' => 'balance'),
	);

	$data_temp = array();
	$hide_missing_data = !require_get("debug_show_missing_data", false);
	$latest = array();
	foreach ($sources as $source) {
		$q = db()->prepare($source['query']);
		$q->execute(array(
			'user_id' => $user_id,
			'currency' => $currency,
		));
		while ($ticker = $q->fetch()) {
			$key = date('Y-m-d', strtotime($ticker[$source['key']]));
			if (!isset($data_temp[$key])) {
				$data_temp[$key] = array();
			}
			if (!isset($data_temp[$key][$ticker['exchange']])) {
				$data_temp[$key][$ticker['exchange']] = 0;
			}
			$data_temp[$key][$ticker['exchange']] += $ticker[$source['balance_key']];
			$last_updated = max($last_updated, strtotime($ticker['created_at']));
			$exchanges_found[$ticker['exchange']] = $ticker['exchange'];
			if (!isset($maximum_balances[$ticker['exchange']])) {
				$maximum_balances[$ticker['exchange']] = 0;
			}
			$maximum_balances[$ticker['exchange']] = max($ticker[$source['balance_key']], $maximum_balances[$ticker['exchange']]);
			if (!isset($latest[$ticker['exchange']])) {
				$latest[$ticker['exchange']] = 0;
			}
			$latest[$ticker['exchange']] = max($latest[$ticker['exchange']], strtotime($ticker[$source['key']]));
		}
	}

	// get rid of any exchange summaries that had zero data
	foreach ($maximum_balances as $key => $balance) {
		if ($balance == 0) {
			foreach ($data_temp as $dt_key => $values) {
				unset($data_temp[$dt_key][$key]);
			}
			unset($exchanges_found[$key]);
		}
	}

	// sort by date so we can get previous dates if necessary for missing data
	ksort($data_temp);

	$data = array();

	// add headings after we know how many exchanges we've found
	$headings = array("Date");
	$i = 0;
	// sort them so they're always in the same order
	ksort($exchanges_found);
	foreach ($exchanges_found as $key => $ignored) {
		$headings[] = array(
			'title' => get_exchange_name($key),
			'line_width' => 2,
			'color' => default_chart_color($i++),
		);
	}
	$data[0] = $headings;

	// add '0' for exchanges that we've found at one point, but don't have a data point
	// but reset to '0' for exchanges that are no longer present (i.e. from graph_data_balances archives)
	// this fixes a bug where old securities data is still displayed as present in long historical graphs
	$previous_row = array();
	foreach ($data_temp as $date => $values) {
		$row = array('new Date(' . date('Y, n-1, j', strtotime($date)) . ')',);
		foreach ($exchanges_found as $key => $ignored) {
			if (!$hide_missing_data || strtotime($date) <= $latest[$key]) {
				if (!isset($values[$key])) {
					$row[$key] = graph_number_format(isset($previous_row[$key]) ? $previous_row[$key] : 0);
				} else {
					$row[$key] = graph_number_format(demo_scale($values[$key]));
				}
			} else {
				$row[$key] = graph_number_format(0);
			}
		}
		if (count($row) > 1) {
			// don't add empty rows
			$data[$date] = $row;
			$previous_row = $row;
		}
	}

	$graph['last_updated'] = $last_updated;

	if (count($data) > 1) {
		render_linegraph_date($graph, array_values($data));
	} else {
		if ($user_id == get_site_config('system_user_id')) {
			render_text($graph, "Invalid balance type.");	// or there is no data to display
		} else {
			render_text($graph, "Either you have not enabled this balance, or your summaries for this balance have not yet been updated.
						<br><a href=\"" . htmlspecialchars(url_for('wizard_currencies')) . "\">Configure currencies</a>");
		}
	}

}

function render_external_graph($graph) {

	if (!isset($graph['arg0_resolved'])) {
		$q = db()->prepare("SELECT * FROM external_status_types WHERE id=?");
		$q->execute(array($graph['arg0']));
		$resolved = $q->fetch();
		if (!$resolved) {
			render_text($graph, "Invalid external status type ID.");
			return;
		} else {
			$graph['arg0_resolved'] = $resolved['job_type'];
		}
	}
	$job_type = $graph['arg0_resolved'];

	$data = array();
	$data[0] = array("Date",
		array(
			'title' => "% success",
			'line_width' => 2,
			'color' => default_chart_color(0),
			'min' => 0,
			'max' => 100,
		),
	);
	$last_updated = false;
	$days = get_graph_days($graph);
	$extra_days = extra_days_necessary($graph);

	$sources = array(
		// we can't LIMIT by days here, because we don't have is_daily_data => multiple points for one day
		// TODO first get summarised data
		// and then get more recent data
		// TODO this gets ALL data (24 points a day); should summarise instead
		/*
		array('query' => "SELECT * FROM external_status WHERE is_daily_data=1 AND job_type=:job_type
			ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at', 'balance_key' => 'balance'),
			*/
		array('query' => "SELECT * FROM external_status WHERE job_type=:job_type
			AND created_at >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days + 1) . " DAY)
			ORDER BY is_recent ASC, created_at DESC", 'key' => 'created_at', 'balance_key' => 'balance'),
	);

	foreach ($sources as $source) {
		$q = db()->prepare($source['query']);
		$q->execute(array(
			'job_type' => $job_type,
		));
		while ($ticker = $q->fetch()) {
			$data[date('Y-m-d', strtotime($ticker[$source['key']]))] = array(
				'new Date(' . date('Y, n-1, j', strtotime($ticker[$source['key']])) . ')',
				graph_number_format(100 * (1 - ($ticker['job_errors'] / $ticker['job_count']))),
			);
			$last_updated = max($last_updated, strtotime($ticker['created_at']));
		}
	}

	// discard early data
	$data = discard_early_data($data, $days);

	// sort by key, but we only want values
	uksort($data, 'cmp_time');
	$graph['subheading'] = format_subheading_values($graph, $data, "%");
	$graph['last_updated'] = $last_updated;

	if (count($data) > 1) {
		render_linegraph_date($graph, array_values($data));
	} else {
		render_text($graph, "There is not yet any historical data for this external API.");
	}

}

function render_site_statistics_queue($graph) {

	if (!is_admin()) {
		render_text("This graph is for administrators only.");
		return;
	}

	$data = array();
	$data[0] = array("Date",
		array(
			'title' => " Free delay",
			'line_width' => 2,
			'color' => default_chart_color(0),
		),
		array(
			'title' => " Premium delay",
			'line_width' => 2,
			'color' => default_chart_color(1),
		),
	);
	$last_updated = false;
	$days = get_graph_days($graph);
	$extra_days = extra_days_necessary($graph);

	$sources = array(
		array('query' => "SELECT * FROM site_statistics
			ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at'),
	);

	foreach ($sources as $source) {
		$q = db()->prepare($source['query']);
		$q->execute();
		while ($ticker = $q->fetch()) {
			$data[date('Y-m-d H-i-s', strtotime($ticker[$source['key']]))] = array(
				'new Date(' . date('Y, n-1, j, H, i, s', strtotime($ticker[$source['key']])) . ')',
				graph_number_format($ticker['free_delay_minutes'] / 60),
				graph_number_format($ticker['premium_delay_minutes'] / 60),
			);
			$last_updated = max($last_updated, strtotime($ticker['created_at']));
		}
	}

	$graph['last_updated'] = $last_updated;

	if (count($data) > 1) {
		render_linegraph_date($graph, array_values($data));
	} else {
		render_text($graph, "There is not yet any historical data for these statistics.");
	}

}
