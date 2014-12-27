<?php
require_admin();
?>

<table class="standard">
<thead>
	<tr>
		<th>ID</th>
		<th class="date">Date</th>
		<th>Type</th>
		<th>Message</th>
		<th>Source</th>
		<th>Job ID</th>
		<th></th>
	</tr>
</thead>
<tbody>
<?php
	$q = db()->prepare("SELECT uncaught_exceptions.*, jobs.job_type FROM uncaught_exceptions
		LEFT JOIN jobs ON uncaught_exceptions.job_id=jobs.id
		ORDER BY id DESC LIMIT " . ((int) $limit));
	$q->execute();
	while ($e = $q->fetch()) {
		$path = str_replace("\\", "/", $e['filename']); ?>
	<tr>
		<td><?php echo number_format($e['id']); ?></td>
		<td class="date"><?php echo recent_format_html($e['created_at']); ?></td>
		<td><?php echo htmlspecialchars($e['class_name']); ?></td>
		<td><?php echo htmlspecialchars($e['message']); ?></td>
		<td><?php echo htmlspecialchars(substr($path, strrpos($path, '/') + 1) . ":" . $e['line_number']); ?></td>
		<td><?php echo htmlspecialchars($e['job_id']); echo $e['job_type'] ? (": " . htmlspecialchars($e['job_type'])) : ""; ?></td>
		<td><?php if ($e['job_id']) { ?><a href="<?php echo htmlspecialchars(url_for('admin_run_job', array('job_id' => $e['job_id'], 'force' => 1))); ?>">Run again</a><?php } ?></td>
	</tr>
	<?php }
?>
</tbody>
</table>