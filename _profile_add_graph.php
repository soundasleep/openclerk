<div id="edit_graph_form">
<form action="<?php echo htmlspecialchars(url_for('profile_add_graph')); ?>" method="post">
<table class="form">
<tr>
	<th>Graph type:</th>
	<td><select name="type" id="graph_type">
		<option id="graph_type_template">Loading...</option>
	</select></td>
</tr>
<tr id="add_graph_arg0" style="display:none;">
	<th>Argument:</th>
	<td><select name="arg0" id="graph_arg0">
		<option value="" id="graph_arg0_template">Loading...</option>
	</select></td>
</tr>
<tr id="add_graph_string0" style="display:none;">
	<th>Argument:</th>
	<td><input name="string0" id="graph_string0" size="32" maxlength="128" value="Loading..."></td>
</tr>
<?php
$size_options = array('width' => 'Width', 'height' => 'Height');
foreach ($size_options as $size_key => $size_value) { ?>
<tr>
	<th><?php echo htmlspecialchars($size_value); ?>:</th>
	<td><select name="<?php echo htmlspecialchars($size_key); ?>">
		<?php
			$options = array(1 => "Small", 2 => "Medium", 4 => "Large", 5 => "Larger", 6 => "Very Large");
			foreach ($options as $key => $value) {
				echo '<option value="' . htmlspecialchars($key) . '"' . (get_site_config('default_user_graph_' . $size_key, 4) == $key ? ' selected' : '') . '>' . htmlspecialchars($value) . ' (' . number_format(get_site_config('default_graph_' . $size_key) * $key) . 'px)</option>';
			}
		?>
	</select></td>
</tr>
<?php } ?>
<tr id="add_graph_days" style="display:none;">
	<th>Days:</th>
	<td><select name="days">
<?php foreach (get_permitted_days() as $key => $days) { ?>
		<option value="<?php echo htmlspecialchars($days['days']); ?>"<?php echo get_site_config('default_user_graph_days') == $days['days'] ? " selected" : ""; ?>><?php echo htmlspecialchars($days['title']); ?></option>
<?php } ?>
	</select></td>
</tr>
<tr id="add_graph_delta" style="display:none;">
	<th>Show:</th>
	<td><select name="delta">
<?php foreach (get_permitted_deltas() as $key => $days) { ?>
		<option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($days['description']); ?></option>
<?php } ?>
	</select></td>
</tr>
<tr id="add_graph_technical" style="display:none;">
	<th>Technical:</th>
	<td><select name="technical" id="graph_technical">
		<option value="">(none)</option>
		<option id="graph_technical_template">Loading...</option>
	</select>
<?php if (!$user['is_premium']) { ?>
	<div class="tip" id="premium_warning" style="display:none;">This technical analysis tool requires a <a href="<?php echo htmlspecialchars(url_for('premium')); ?>" class="premium">premium account</a>.</div>
<?php } ?>
	</td>
</tr>
<tr id="add_graph_period" style="display:none;">
	<th></th>
	<td>
		<label>Period: <input type="text" name="period" value="10" size="6"> days</label>
	</td>
</tr>
<tr>
	<td colspan="2" class="buttons">
		<input type="hidden" name="page" value="<?php echo htmlspecialchars($page_id); ?>">
		<input type="submit" value="Add graph">
		<input type="hidden" name="id" value="">

		<div class="managed-notice">
		<?php if ($graph_page['is_managed'] && $user['graph_managed_type'] == 'managed') { ?>
			These graphs are currently <a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>">managed based on your portfolio preferences</a>.
		<?php } else { ?>
			You can also <a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>">add graphs based on your portfolio preferences</a>.
		<?php } ?>
		</div>

	</td>
</tr>
</table>

<div id="graph_description">Select an option</div>
</form>
</div>

<script type="text/javascript">
function graph_types() {
	return [
<?php foreach (graph_types() as $id => $graph) {
	if (!(isset($graph['hide']) && $graph['hide'])) {
		// we don't want to display graph types that we aren't interested in
		$arg0 = (isset($graph['arg0']) && $graph['arg0']) ? $graph['arg0'] : false;
		$arg0_values = $arg0 ? $arg0(isset($graph['param0']) ? $graph['param0'] : false, isset($graph['param1']) ? $graph['param1'] : false) : false;
		if ($arg0_values) {
			// need to convert from array of (id => value) to a list of {id, value}, because JS
			// sorts by id whereas PHP sorts by insertion order
			$result = array();
			foreach ($arg0_values as $key => $value) {
				$result[] = array($key, $value);
			}
			$arg0_values = $result;
		}
		if (!($arg0 && !$arg0_values)) {
			// we also don't want to display graph types that need arguments, but there aren't any
			if (isset($graph['category']) && $graph['category']) {
				echo "{ 'category' : " . json_encode($graph['title']) . " },\n";
			} else {
				echo "{ 'id' : " . json_encode($id) . ", 'title' : " . json_encode($graph['title']) . ", 'description' : " .  json_encode($graph['description']) .
					((isset($graph['technical']) && $graph['technical']) ? ", 'technical': true" : "") .
					((isset($graph['arg0_title']) && $graph['arg0_title']) ? ", 'arg0_title': " . json_encode($graph['arg0_title']) : "") .
					($arg0 ? ", 'arg0': " . json_encode($arg0_values) : "") .
					", 'string0': " . ((isset($graph['string0']) && $graph['string0']) ? json_encode($graph['string0']) : "null") .
					", 'days': " . json_encode(isset($graph['days'])) . ", 'delta': " . json_encode(isset($graph['delta'])) . "},\n";
			}
		}
	}
} ?>
	];
}

function graph_technical_types() {
	return [
<?php foreach (graph_technical_types() as $id => $data) {
	echo "{ 'id' : '" . htmlspecialchars($id) . "', 'title' : '" . htmlspecialchars($data['title']) . "'" .
			", 'description' : " . json_encode($data['description']) .
			", 'premium' : " . ($data['premium'] ? "true" : "false") .
			", 'period' : " . ($data['period'] ? "true" : "false") . "},\n";
} ?>
	];
}

function user_has_premium() {
	return <?php echo $user['is_premium'] ? "true" : "false"; ?>;
}
</script>