<?php

/**
 * Renders a particular graph.
 */
function render_graph($graph) {

	$graph_types = graph_types();
	$graph_type = $graph_types[$graph['graph_type']];
	if (!$graph_type) {
		throw new GraphException("Unknown graph type " . htmlspecialchars($graph['graph_type']));
	}

	echo "<h2>" . htmlspecialchars(isset($graph_type['heading']) ? $graph_type['heading'] : $graph_type['title']) . "</h2>\n";
	render_graph_controls($graph);

	$add_more_currencies = "<a href=\"" . htmlspecialchars(url_for('user')) . "\">Add more currencies</a>";

	switch ($graph['graph_type']) {

		case "btc_equivalent":
			// a pie chart

			// get all balances
			$balances = get_all_summary_instances();

			// and convert them using the most recent rates
			$rates = get_all_recent_rates();

			// create data
			$data = array();
			if (isset($balances['totalbtc']) && $balances['totalbtc']['balance'] != 0) {
				$data['BTC'] = ($balances['totalbtc']['balance']);
			}
			if (isset($balances['totalltc']) && $balances['totalltc']['balance'] != 0 && isset($rates['btcltc'])) {
				$data['LTC'] = ($balances['totalltc']['balance'] * $rates['btcltc']['sell']);
			}
			if (isset($balances['totalnmc']) && $balances['totalnmc']['balance'] != 0 && isset($rates['btcnmc'])) {
				$data['NMC'] = ($balances['totalnmc']['balance'] * $rates['btcnmc']['sell']);
			}
			if (isset($balances['totalusd']) && $balances['totalusd']['balance'] != 0 && isset($rates['usdbtc']) && $rates['usdbtc'] /* no div by 0 */) {
				$data['USD'] = ($balances['totalusd']['balance'] / $rates['usdbtc']['buy']);
			}
			if (isset($balances['totalnzd']) && $balances['totalnzd']['balance'] != 0 && isset($rates['nzdbtc']) && $rates['nzdbtc'] /* no div by 0 */) {
				$data['NZD'] = ($balances['totalnzd']['balance'] / $rates['nzdbtc']['buy']);
			}

			if ($data) {
				render_pie_chart($graph, $data, 'Currency', 'BTC', 'graph_format_btc');
			} else {
				render_text($graph, "Either you have not specified any accounts or addresses, or these addresses and accounts have not yet been updated.
					<br><a href=\"" . htmlspecialchars(url_for('accounts')) . "\">Add accounts and addresses</a>");
			}
			break;

		case "mtgox_btc_table":
			// a table of just BTC/USD rates
			$rates = get_all_recent_rates();

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

			// create data
			$data = array();
			foreach ($currencies as $c) {
				if (isset($summaries[$c])) {
					$balance = isset($balances['total'.$c]) ? $balances['total'.$c]['balance'] : 0;
					$data[] = array(strtoupper($c), currency_format($c, $balance, 4));
				}
			}

			$graph["extra"] = $add_more_currencies;
			render_table_vertical($graph, $data);
			break;

		case "fiat_converted_table":
			// a table of each all2fiat value
			// get all balances
			$balances = get_all_summary_instances();
			$summaries = get_all_summary_currencies();
			$currencies = get_all_currencies();

			// create data
			$data = array();
			foreach ($currencies as $c) {
				if (isset($summaries[$c])) {
					$key = $summaries[$c];
					$key1 = "all2" . substr($key, strlen("summary_"));
					$balance = isset($balances[$key1]['balance']) ? $balances[$key1]['balance'] : 0;
					$data[] = array(get_summary_types()[$key]['short_title'], currency_format($c, $balance, 4));
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

			// create data
			$data = array();
			$data[] = array('Currency', 'Balance', 'Offset', 'Total');
			foreach ($currencies as $c) {
				if (isset($summaries[$c])) {
					$balance = isset($balances['total'.$c]) ? $balances['total'.$c]['balance'] : 0;
					$offset = isset($offsets[$c]) ? $offsets[$c]['balance'] : 0;
					$data[] = array(
						strtoupper($c),
						currency_format($c, $balance - $offset, 4),
						// HTML quirk: "When there is only one single-line text input field in a form, the user agent should accept Enter in that field as a request to submit the form."
						'<form action="' . htmlspecialchars(url_for('set_offset', array('page' => $graph['page_id']))) . '" method="post">' .
							'<input type="text" name="' . htmlspecialchars($c) . '" value="' . htmlspecialchars($offset == 0 ? '' : $offset) . '" maxlength="32">' .
							'</form>',
						currency_format($c, $balance /* balance includes offset */, 4),
					);
				}
			}

			$graph['extra'] = $add_more_currencies;

			render_table_horizontal_vertical($graph, $data);
			break;

		case "linebreak":
			// implemented by profile.php
			break;

		default:
			throw new GraphException("Couldn't render graph type " . htmlspecialchars($graph['graph_type']));
	}

}

/**
 * Get all of the defined graph types. Used for display and validation.
 */
function graph_types() {
	return array(
		'btc_equivalent' => array('title' => 'Equivalent BTC balances (pie)', 'heading' => 'Equivalent BTC', 'description' => 'A pie chart representing the overall value of all accounts if they were all converted into BTC.<p>Exchanges used: BTC-E for LTC/NMC, Mt.Gox for USD, BitNZ for NZD'),
		'mtgox_btc_table' => array('title' => 'Mt.Gox USD/BTC (table)', 'heading' => 'Mt.Gox BTC', 'description' => 'A simple table displaying the current buy/sell USD/BTC price.'),
		'balances_table' => array('title' => 'Total balances (table)', 'heading' => 'Total balances', 'description' => 'A table displaying the current sum of all currencies (before any conversion).'),
		'fiat_converted_table' => array('title' => 'Converted fiat balances (table)', 'heading' => 'Converted fiat', 'description' => 'A table displaying the equivalent value of all cryptocurrencies - and not other fiat currencies - if they were immediately converted into fiat currencies via BTC.<p>Exchanges used: BTC-E for LTC/NMC, Mt.Gox for USD, BitNZ for NZD'),
		"balances_offset_table" => array('title' => 'Total balances with offsets (table)', 'heading' => 'Total balances', 'description' => 'A table displaying the current sum of all currencies (before any conversion), along with text fields to set offset values for each currency directly.'),

		'linebreak' => array('title' => 'Line break', 'description' => 'Forces a line break at a particular location. Select \'Enable layout editing\' to move it.'),
	);
}

function render_text($graph, $text) {
	$graph_id = htmlspecialchars($graph['id']);
?>
<div id="graph_<?php echo $graph_id; ?>"<?php echo get_dimensions($graph); ?>>
<?php echo $text; ?>
<?php if (isset($graph['extra'])) echo '<div class=\"graph_extra\">' . $graph['extra'] . '</div>'; ?>
</div>
<?php
}

function render_table_vertical($graph, $data) {
	$graph_id = htmlspecialchars($graph['id']);
?>
<div id="graph_<?php echo $graph_id; ?>"<?php echo get_dimensions($graph); ?>>
<table class="standard">
<?php foreach ($data as $row) {
	echo "<tr>";
	foreach ($row as $i => $item) {
		echo ($i == 0 ? "<th>" : "<td>");
		echo $item;	// assumed to be html escaped
		echo ($i == 0 ? "</th>" : "</td>");
	}
	echo "</tr>\n";
}
?>
</table>
<?php if (isset($graph['extra'])) echo '<div class=\"graph_extra\">' . $graph['extra'] . '</div>'; ?>
</div>
<?php
}

function render_table_horizontal_vertical($graph, $data) {
	$graph_id = htmlspecialchars($graph['id']);
?>
<div id="graph_<?php echo $graph_id; ?>"<?php echo get_dimensions($graph); ?>>
<table class="standard">
<?php foreach ($data as $rowid => $row) {
	echo "<tr>";
	foreach ($row as $i => $item) {
		echo (($i == 0 || $rowid == 0) ? "<th>" : "<td>");
		echo $item;	// assumed to be html escaped
		echo (($i == 0 || $rowid == 0) ? "</th>" : "</td>");
	}
	echo "</tr>\n";
}
?>
</table>
<?php if (isset($graph['extra'])) echo '<div class=\"graph_extra\">' . $graph['extra'] . '</div>'; ?>
</div>
<?php
}

function graph_format_btc($value) {
	return number_format($value, 4, '.', '');
}

function render_graph_controls($graph) {
?>
<ul class="graph_controls">
	<li class="move_up"><a href="<?php echo htmlspecialchars(url_for('profile', array(
		'page' => $graph['page_id'],
		'move_up' => $graph['id']))); ?>">Move up</a></li>
	<li class="move_down"><a href="<?php echo htmlspecialchars(url_for('profile', array(
		'page' => $graph['page_id'],
		'move_down' => $graph['id']))); ?>">Move down</a></li>
	<li class="remove"><a href="<?php echo htmlspecialchars(url_for('profile', array(
		'page' => $graph['page_id'],
		'remove' => $graph['id']))); ?>">Remove</a></li>
</ul>
<?php
}

/**
 * @param $data an associative array of (label => numeric value)
 */
function render_pie_chart($graph, $data, $key_label, $value_label, $callback = 'number_format') {
	$graph_id = htmlspecialchars($graph['id']);
?>
<script type="text/javascript">
  google.load("visualization", "1", {packages:["corechart"]});
  google.setOnLoadCallback(drawChart<?php echo $graph_id; ?>);
  function drawChart<?php echo $graph_id; ?>() {
	var data = google.visualization.arrayToDataTable([
	  ['<?php echo htmlspecialchars($key_label); ?>', '<?php echo htmlspecialchars($value_label); ?>'],
	  <?php
	  foreach ($data as $key => $value) {
		echo "['" . $key . "', " . $callback($value) . "],";
	  } ?>
	]);

	var options = {
//          title: '...'
		legend: {position: 'none'},
		backgroundColor: '#111',
	};

	var chart = new google.visualization.PieChart(document.getElementById('graph_<?php echo $graph_id; ?>'));
	chart.draw(data, options);
  }
</script>

<div id="graph_<?php echo $graph_id; ?>"<?php echo get_dimensions($graph); ?>></div>
<?php if (isset($graph['extra'])) echo '<div class=\"graph_extra\">' . $graph['extra'] . '</div>'; ?>
<?php
}

function get_dimensions($graph) {
	return ' style="width: ' . (get_site_config('default_graph_width') * $graph['width']) . 'px; height: ' . (get_site_config('default_graph_height') * $graph['height']) . 'px;"';
}

// cached
$global_all_summary_instances = null;
function get_all_summary_instances() {
	global $global_all_summary_instances;
	if ($global_all_summary_instances === null) {
		$global_all_summary_instances = array();
		$q = db()->prepare("SELECT * FROM summary_instances WHERE user_id=? AND is_recent=1");
		$q->execute(array(user_id()));
		while ($summary = $q->fetch()) {
			$global_all_summary_instances[$summary['summary_type']] = $summary;
		}
	}
	return $global_all_summary_instances;
}

// cached
$global_all_summaries = null;
function get_all_summaries() {
	global $global_all_summaries;
	if ($global_all_summaries === null) {
		$global_all_summaries = array();
		$q = db()->prepare("SELECT * FROM summaries WHERE user_id=?");
		$q->execute(array(user_id()));
		while ($summary = $q->fetch()) {
			$global_all_summaries[$summary['summary_type']] = $summary;
		}
	}
	return $global_all_summaries;
}

// cached
$global_all_offset_instances = null;
function get_all_offset_instances() {
	global $global_all_offset_instances;
	if ($global_all_offset_instances === null) {
		$global_all_offset_instances = array();
		$q = db()->prepare("SELECT * FROM offsets WHERE user_id=? AND is_recent=1");
		$q->execute(array(user_id()));
		while ($offset = $q->fetch()) {
			$global_all_offset_instances[$offset['currency']] = $offset;
		}
	}
	return $global_all_offset_instances;
}

function get_all_summary_currencies() {
	$summaries = get_all_summaries();
	$result = array();
	foreach ($summaries as $s) {
		// assumes all summaries start with 'summary_CUR_optional'
		$c = substr($s['summary_type'], strlen("summary_"), 3);
		$result[$c] = $s['summary_type'];
	}
	return $result;
}

// cached
$global_all_recent_rates = null;
// this also makes assumptions about which is the best exchange for each rate
// e.g. btc-e for btc/ltc, mtgox for usd/btc
function get_all_recent_rates() {
	global $global_all_recent_rates;
	if ($global_all_recent_rates === null) {
		$global_all_recent_rates = array();
		$q = db()->prepare("SELECT * FROM ticker WHERE is_recent=1 AND (
			(currency1 = 'btc' AND currency2 = 'ltc' AND exchange='btce') OR
			(currency1 = 'btc' AND currency2 = 'nmc' AND exchange='btce') OR
			(currency1 = 'nzd' AND currency2 = 'btc' AND exchange='bitnz') OR
			(currency1 = 'usd' AND currency2 = 'btc' AND exchange='mtgox') OR
			0
		)");
		$q->execute(array(user_id()));
		while ($ticker = $q->fetch()) {
			$global_all_recent_rates[$ticker['currency1'] . $ticker['currency2']] = $ticker;
		}
	}
	return $global_all_recent_rates;
}

class GraphException extends Exception { }