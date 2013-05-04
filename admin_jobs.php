<?php

/**
 * Admin status page: jobs
 */

require("inc/global.php");
require_admin();

require("layout/templates.php");

$messages = array();
$errors = array();

page_header("Admin: Jobs Status", "page_admin_jobs");

?>

<h1>Jobs Status</h1>

<p><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<table class="standard">
<thead>
	<tr>
		<th>Job ID</th>
		<th>Priority</th>
		<th>Job type</th>
		<th>Created at</th>
		<th>User</th>
		<th></th>
	</tr>
</thead>
<tbody>
<?php
	$q = db()->prepare("SELECT jobs.*, users.email FROM jobs
		LEFT JOIN users ON jobs.user_id=users.id
		WHERE is_executed=0 ORDER BY priority ASC, id ASC LIMIT 20");
	$q->execute();
	while ($job = $q->fetch()) { ?>
	<tr>
		<td><?php echo number_format($job['id']); ?></td>
		<td><?php echo number_format($job['priority']); ?></td>
		<td><?php echo htmlspecialchars($job['job_type']); ?> (<?php echo htmlspecialchars($job['arg_id']); ?>)</td>
		<td><?php echo recent_format_html($job['created_at']); ?></td>
		<td><?php echo $job['email'] ? htmlspecialchars($job['email']) : htmlspecialchars($job['user_id']); ?></td>
		<td><a href="<?php echo htmlspecialchars(url_for('admin_run_job', array('job_id' => $job['id']))); ?>">Run now</a></td>
	</tr>
	<?php }
?>
</tbody>
</table>

<?php
page_footer();
