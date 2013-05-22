<?php

function render_text($graph, $text) {
	$graph_id = htmlspecialchars($graph['id']);
	render_graph_last_updated($graph);
?>
<div id="graph_<?php echo $graph_id; ?>"<?php echo get_dimensions($graph); ?>>
<div class="overflow_wrapper">
<?php echo $text; ?>
<?php if (isset($graph['extra'])) echo '<div class="graph_extra">' . $graph['extra'] . '</div>'; ?>
</div>
</div>
<?php
}

function render_table_vertical($graph, $data) {
	$graph_id = htmlspecialchars($graph['id']);
	render_graph_last_updated($graph);
?>
<div id="graph_<?php echo $graph_id; ?>"<?php echo get_dimensions($graph); ?>>
<div class="overflow_wrapper">
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
<?php if (isset($graph['extra'])) echo '<div class="graph_extra">' . $graph['extra'] . '</div>'; ?>
</div>
</div>
<?php
}

function render_table_horizontal_vertical($graph, $data) {
	$graph_id = htmlspecialchars($graph['id']);
	render_graph_last_updated($graph);
?>
<div id="graph_<?php echo $graph_id; ?>"<?php echo get_dimensions($graph); ?>>
<div class="overflow_wrapper">
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
<?php if (isset($graph['extra'])) echo '<div class="graph_extra">' . $graph['extra'] . '</div>'; ?>
</div>
</div>
<?php
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

function render_graph_last_updated($graph) {
	if (isset($graph['last_updated']) && $graph['last_updated'] && $graph['width'] > 1) { ?>
		<div class="last_updated"><?php echo recent_format_html($graph['last_updated']); ?></div>
	<?php }
}

/**
 * @param $data an associative array of (label => numeric value)
 */
function render_pie_chart($graph, $data, $key_label, $value_label, $callback = 'graph_number_format') {
	$graph_id = htmlspecialchars($graph['id']);
	render_graph_last_updated($graph);
?>
<script type="text/javascript">
  google.load("visualization", "1", {packages:["corechart"]});
  google.setOnLoadCallback(drawChart<?php echo $graph_id; ?>);
  function drawChart<?php echo $graph_id; ?>() {
	var data = google.visualization.arrayToDataTable([
	  ['<?php echo htmlspecialchars($key_label); ?>', '<?php echo htmlspecialchars($value_label); ?>'],
	  <?php
	  foreach ($data as $key => $value) {
		echo "[" . json_encode($key) . ", " . $callback($value) . "],";
	  } ?>
	]);

	var options = {
//          title: '...'
		legend: {position: 'none'},
		backgroundColor: '#111',
		chartArea: { width: '75%', height: '75%' },
	};

	var chart = new google.visualization.PieChart(document.getElementById('graph_<?php echo $graph_id; ?>'));
	chart.draw(data, options);
  }
</script>

<div id="graph_<?php echo $graph_id; ?>"<?php echo get_dimensions($graph); ?>></div>
<?php if (isset($graph['extra'])) echo '<div class="graph_extra">' . $graph['extra'] . '</div>'; ?>
<?php
}

function render_linegraph_date($graph, $data) {
	$graph_id = htmlspecialchars($graph['id']);
	render_graph_last_updated($graph);
?>
<script type="text/javascript">
  google.load("visualization", "1", {packages:["corechart"]});
  google.setOnLoadCallback(drawChart<?php echo $graph_id; ?>);
  function drawChart<?php echo $graph_id; ?>() {
        var data = new google.visualization.DataTable();
        data.addColumn('date', 'Date');
        <?php for ($i = 1; $i < count($data[0]); $i++) {
        	$heading = $data[0][$i];
        	$heading = isset($heading['title']) ? $heading['title'] : $heading; ?>
        	data.addColumn('number', <?php echo json_encode($heading); ?>);
        <?php } ?>

        data.addRows([
        <?php for ($i = 1; $i < count($data); $i++) {
        	echo "[" . implode(", ", $data[$i]) . "],\n";
        } ?>
        ]);

        var options = {
			legend: {position: 'none'},
			hAxis: {
				gridlines: { color: '#333' },
				textStyle: { color: 'white' },
				format: 'd-MMM',
			},
			vAxis: {
				gridlines: { color: '#333' },
				textStyle: { color: 'white' },
			},
			series: [
				<?php for ($i = 1; $i < count($data[0]); $i++) {
					$heading = $data[0][$i];
					echo "{";
					$bits = array();
					if (isset($heading['line_width'])) {
						$bits[] = "lineWidth: " . json_encode($heading['line_width']);
					}
					if (isset($heading['color'])) {
						$bits[] = "color: " . json_encode($heading['color']);
					}
					if (isset($heading['axis'])) {
						$bits[] = "targetAxisIndex: " . json_encode($heading['axis']);
					}
					echo implode(",", $bits);
					echo "},";
				} ?>
			],
			<?php if ($graph['width'] >= 8) { ?>
			chartArea: { width: '90%', height: '85%', top: 20, left: <?php echo min(60, 30 * $graph['width']); ?> }, /* reduce padding */
			<?php } else { ?>
			chartArea: { width: '80%', height: '75%', top: 20, left: <?php echo min(60, 30 * $graph['width']); ?> }, /* reduce padding */
			<?php } ?>
			backgroundColor: '#111',
        };

	var chart = new google.visualization.LineChart(document.getElementById('graph_<?php echo $graph_id; ?>'));
	chart.draw(data, options);
  }
</script>

<div id="graph_<?php echo $graph_id; ?>"<?php echo get_dimensions($graph); ?>></div>
<?php if (isset($graph['extra'])) echo '<div class="graph_extra">' . $graph['extra'] . '</div>'; ?>
<?php
}

function get_dimensions($graph) {
	return ' style="width: ' . (get_site_config('default_graph_width') * $graph['width']) . 'px; height: ' . (get_site_config('default_graph_height') * $graph['height']) . 'px;"';
}
