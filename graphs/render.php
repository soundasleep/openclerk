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
	$last_updated = false;
	$days = get_graph_days($graph);
	$extra_days = extra_days_necessary($graph);

	$sources = array(
		// cannot use 'LIMIT :limit'; PDO escapes :limit into string, MySQL cannot handle or cast string LIMITs
		// first get summarised data
		array('query' => "SELECT * FROM graph_data_ticker WHERE exchange=:exchange AND
			currency1=:currency1 AND currency2=:currency2 AND data_date > DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date", 'key' => 'data_date'),
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
			$data[date('Y-m-d', strtotime($ticker[$source['key']]))] = array(
				'new Date(' . date('Y, n-1, j', strtotime($ticker[$source['key']])) . ')',
				graph_number_format($ticker['buy']),
				graph_number_format($ticker['sell']),
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
	$graph['last_updated'] = $last_updated;
	render_linegraph_date($graph, array_values($data));
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
			data_date >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date", 'key' => 'data_date', 'balance_key' => 'balance_closing'),
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
				graph_number_format($ticker[$source['balance_key']]),
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
	$graph['last_updated'] = $last_updated;

	if (count($data) > 1) {
		render_linegraph_date($graph, array_values($data));
	} else {
		render_text($graph, "Either you have not enabled this currency, or your summaries for this currency have not yet been updated.
					<br><a href=\"" . htmlspecialchars(url_for('user')) . "\">Configure currencies</a>");
	}

}

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
			data_date >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date", 'key' => 'data_date', 'balance_key' => 'balance_closing'),
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
				graph_number_format($ticker[$source['balance_key']]),
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
	$graph['last_updated'] = $last_updated;

	if (count($data) > 1) {
		render_linegraph_date($graph, array_values($data));
	} else {
		if ($user_id == get_site_config('system_user_id')) {
			render_text($graph, "Invalid balance type.");
		} else {
			render_text($graph, "Either you have not enabled this balance, or your summaries for this balance have not yet been updated.
						<br><a href=\"" . htmlspecialchars(url_for('user')) . "\">Configure currencies</a>");
		}
	}

}

function render_external_graph($graph) {

	$job_type = $graph['arg0'];

	$data = array();
	$data[0] = array("Date",
		array(
			'title' => "% success",
			'line_width' => 2,
			'color' => default_chart_color(0),
		),
	);
	$last_updated = false;
	$days = get_graph_days($graph);
	$extra_days = extra_days_necessary($graph);

	$sources = array(
		// TODO first get summarised data
		// and then get more recent data
		// TODO this gets ALL data (24 points a day); should summarise instead
		/*
		array('query' => "SELECT * FROM external_status WHERE is_daily_data=1 AND job_type=:job_type
			ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at', 'balance_key' => 'balance'),
			*/
		array('query' => "SELECT * FROM external_status WHERE job_type=:job_type
			ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at', 'balance_key' => 'balance'),
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
	$graph['last_updated'] = $last_updated;

	if (count($data) > 1) {
		render_linegraph_date($graph, array_values($data));
	} else {
		render_text($graph, "There is not yet any historical data for this external API.");
	}

}
