<h1>Help</h1>

<div class="expand_all">
<label><input type="checkbox" id="expand_all"> Expand all answers</input></label>
</div>

<dl class="help_list">
	<dt>When will the help page have answers?</dt>
	<dd>When they are written.</dt>

	<dt>How many answers can we have?</dt>
	<dd>As many as there are questions.</dt>

	<dt>More help</dt>
	<dd>
		For additional support, contact us via:

		<table class="fancy">
		<tr>
			<th><span class="help_name_email">Email</span></th>
			<td><a href="mailto:<?php echo htmlspecialchars(get_site_config('site_email')); ?>"><?php echo htmlspecialchars(get_site_config('site_email')); ?></a></td>
		</tr>
		<tr>
			<th><span class="help_name_forum">Forum</span></th>
			<td><a href="<?php echo htmlspecialchars(get_site_config('forum_link')); ?>">Forum</a></td>
		</tr>
		</table>
	</dd>

</dl>