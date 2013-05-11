<div class="add_graph">
<h2>Add new graph</h2>

<form action="<?php echo htmlspecialchars(url_for('profile_add_graph')); ?>" method="post">
<table class="form">
<tr>
	<th>Graph type:</th>
	<td><select name="type" id="graph_type">
		<option id="graph_type_template">Loading...</option>
	</select></td>
</tr>
<tr>
	<th>Width:</th>
	<td><select name="width">
		<option value="1">Small (<?php echo number_format(get_site_config('default_graph_width') * 1); ?>px)</option>
		<option value="2" selected>Medium (<?php echo number_format(get_site_config('default_graph_width') * 2); ?>px)</option>
		<option value="4">Large (<?php echo number_format(get_site_config('default_graph_width') * 4); ?>px)</option>
		<option value="6">Very Large (<?php echo number_format(get_site_config('default_graph_width') * 6); ?>px)</option>
	</select></td>
</tr>
<tr>
	<th>Height:</th>
	<td><select name="height">
		<option value="1">Small (<?php echo number_format(get_site_config('default_graph_height') * 1); ?>px)</option>
		<option value="2" selected>Medium (<?php echo number_format(get_site_config('default_graph_height') * 2); ?>px)</option>
		<option value="4">Large (<?php echo number_format(get_site_config('default_graph_height') * 4); ?>px)</option>
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
<tr>
	<td colspan="2" class="buttons">
		<input type="hidden" name="page" value="<?php echo htmlspecialchars($page_id); ?>">
		<input type="submit" value="Add graph">
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
		echo "{ 'id' : '" . htmlspecialchars($id) . "', 'title' : '" . htmlspecialchars($graph['title']) . "', 'description' : " .  json_encode($graph['description']) .
			", 'days': " . json_encode(isset($graph['days'])) . "},\n";
	}
} ?>
	];
}
</script>