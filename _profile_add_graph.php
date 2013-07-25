<div class="add_graph">
<h2>Add new graph</h2>

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
<tr>
	<th>Width:</th>
	<td><select name="width">
		<option value="1">Small (<?php echo number_format(get_site_config('default_graph_width') * 1); ?>px)</option>
		<option value="2" selected>Medium (<?php echo number_format(get_site_config('default_graph_width') * 2); ?>px)</option>
		<option value="4">Large (<?php echo number_format(get_site_config('default_graph_width') * 4); ?>px)</option>
		<option value="5">Larger (<?php echo number_format(get_site_config('default_graph_width') * 5); ?>px)</option>
		<option value="6">Very Large (<?php echo number_format(get_site_config('default_graph_width') * 6); ?>px)</option>
	</select></td>
</tr>
<tr>
	<th>Height:</th>
	<td><select name="height">
		<option value="1">Small (<?php echo number_format(get_site_config('default_graph_height') * 1); ?>px)</option>
		<option value="2" selected>Medium (<?php echo number_format(get_site_config('default_graph_height') * 2); ?>px)</option>
		<option value="4">Large (<?php echo number_format(get_site_config('default_graph_width') * 4); ?>px)</option>
		<option value="5">Larger (<?php echo number_format(get_site_config('default_graph_height') * 5); ?>px)</option>
		<option value="6">Very Large (<?php echo number_format(get_site_config('default_graph_height') * 6); ?>px)</option>
	</select></td>
</tr>
<tr id="add_graph_days" style="display:none;">
	<th>Days:</th>
	<td><select name="days">
<?php foreach (get_permitted_days() as $key => $days) { ?>
		<option value="<?php echo htmlspecialchars($days['days']); ?>"><?php echo htmlspecialchars($days['title']); ?></option>
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
	</td>
</tr>
</table>

<div id="graph_description">Select an option</div>
</form>
</div>

</div>

<script type="text/javascript">
function graph_types() {
	return [
<?php foreach (graph_types() as $id => $graph) {
	if (!(isset($graph['hide']) && $graph['hide'])) {
		// we don't want to display graph types that we aren't interested in
		$arg0 = (isset($graph['arg0']) && $graph['arg0']) ? $graph['arg0'] : false;
		$arg0_values = $arg0 ? $arg0() : false;
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
			echo "{ 'id' : " . json_encode($id) . ", 'title' : " . json_encode($graph['title']) . ", 'description' : " .  json_encode($graph['description']) .
				((isset($graph['technical']) && $graph['technical']) ? ", 'technical': true" : "") .
				((isset($graph['arg0_title']) && $graph['arg0_title']) ? ", 'arg0_title': " . json_encode($graph['arg0_title']) : "") .
				($arg0 ? ", 'arg0': " . json_encode($arg0_values) : "") .
				", 'string0': " . ((isset($graph['string0']) && $graph['string0']) ? json_encode($graph['string0']) : "null") .
				", 'days': " . json_encode(isset($graph['days'])) . "},\n";
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