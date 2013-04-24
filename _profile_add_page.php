<div class="add_page">
<h2>Add new page</h2>

<form action="<?php echo htmlspecialchars(url_for('profile_add_page')); ?>" method="post">
<table class="form">
<tr>
	<th>Title:</th>
	<td><input name="title" maxlength="64"></td>
</tr>
<tr>
	<td colspan="2" class="buttons">
		<input type="hidden" name="page" value="<?php echo htmlspecialchars($page_id); ?>">
		<input type="submit" value="Add page">
	</td>
</tr>
</table>
</form>
</div>

<div class="delete_page">
<h2>Remove this page</h2>

<form action="<?php echo htmlspecialchars(url_for('profile_remove_page')); ?>" method="post">
<table class="form">
<tr>
	<th></th>
	<td><label><input type="checkbox" name="confirm" value="1"> Remove this page</label></td>
</tr>
<tr>
	<td colspan="2" class="buttons">
		<input type="hidden" name="page" value="<?php echo htmlspecialchars($page_id); ?>">
		<input type="submit" value="Remove this page">
	</td>
</tr>
</table>
</form>
</div>
