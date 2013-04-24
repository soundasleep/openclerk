<div class="add_graph">
<h2>Add new graph</h2>

<form action="<?php echo htmlspecialchars(url_for('profile_add_graph')); ?>" method="post">
<table class="form">
<tr>
	<th>Graph type:</th>
	<td><select name="type" id="graph_type">
		<option id="graph_type_template">Loading...</option>
	</select></td>
	<td rowspan="3"><div id="graph_description">Select an option</div></td>
</tr>
<tr>
	<th>Width:</th>
	<td><select name="width">
		<option value="1">Small</option>
		<option value="2" selected>Medium</option>
		<option value="4">Large</option>
	</select></td>
</tr>
<tr>
	<th>Height:</th>
	<td><select name="height">
		<option value="1">Small</option>
		<option value="2" selected>Medium</option>
		<option value="4">Large</option>
	</select></td>
</tr>
<tr>
	<td colspan="2" class="buttons">
		<input type="hidden" name="page" value="<?php echo htmlspecialchars($page_id); ?>">
		<input type="submit" value="Add graph">
	</td>
	<td></td>
</tr>
</table>
</form>
</div>

<script type="text/javascript">
function graph_types() {
	return [
<?php foreach (graph_types() as $id => $graph) {
	echo "{ 'id' : '" . htmlspecialchars($id) . "', 'title' : '" . htmlspecialchars($graph['title']) . "', 'description' : " .  json_encode($graph['description']) . "},\n";
} ?>
	];
}
</script>