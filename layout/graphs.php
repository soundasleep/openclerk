<?php

/**
 * Renders a particular graph.
 */
function render_graph($graph) {

	switch ($graph['graph_type']) {

		case "btc_equivalent":
			// a pie chart

			// get all balances
			$balances = get_all_summary_instances();

			// and convert them using the most recent rates
			$rates = get_all_recent_rates();

			// create data
			$data = array();
			if (isset($balances['totalbtc']) && $balances['totalbtc']) {
				$data['BTC'] = ($balances['totalbtc']['balance']);
			}
			if (isset($balances['totalltc']) && $balances['totalltc'] && isset($rates['btcltc'])) {
				$data['LTC'] = ($balances['totalltc']['balance'] * $rates['btcltc']['sell']);
			}
			if (isset($balances['totalnmc']) && $balances['totalnmc'] && isset($rates['btcnmc'])) {
				$data['NMC'] = ($balances['totalnmc']['balance'] * $rates['btcnmc']['sell']);
			}
			if (isset($balances['totalusd']) && $balances['totalusd'] && isset($rates['usdbtc']) && $rates['usdbtc'] /* no div by 0 */) {
				$data['USD'] = ($balances['totalusd']['balance'] / $rates['usdbtc']['buy']);
			}
			if (isset($balances['totalnzd']) && $balances['totalnzd'] && isset($rates['nzdbtc']) && $rates['nzdbtc'] /* no div by 0 */) {
				$data['NZD'] = ($balances['totalnzd']['balance'] / $rates['nzdbtc']['buy']);
			}

			echo "<h2>Equivalent BTC balances</h2>\n";
			render_pie_chart($graph, $data, 'Currency', 'BTC', 'graph_format_btc');
			break;

		default:
			throw new GraphException("Unknown graph type " . $graph['graph_type']);
	}

}

function graph_format_btc($value) {
	return number_format($value, 4, '.', '');
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

<div id="graph_<?php echo $graph_id; ?>" style="width: <?php echo get_site_config('default_graph_width') * $graph['width']; ?>px; height: <?php echo get_site_config('default_graph_height') * $graph['height']; ?>px;"></div>
<?php
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