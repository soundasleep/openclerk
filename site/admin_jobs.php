<?php

/**
 * Admin status page: jobs
 */

require(__DIR__ . "/../inc/global.php");
require_admin();

require(__DIR__ . "/../layout/templates.php");

$messages = array();
$errors = array();

page_header("Admin: Jobs Status", "page_admin_jobs");

?>

<h1>Jobs Status<?php echo require_get("oldest", false) ? " (oldest)" : ""; ?></h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<table class="standard">
<thead>
	<tr>
		<th>Job ID</th>
		<th>Priority</th>
		<th>Job type</th>
		<th>Manual</th>
		<th>Executing</th>
		<th>Attempts</th>
		<th>Created at</th>
		<th>User</th>
		<th></th>
	</tr>
</thead>
<tbody>
<?php
	$order_by = require_get("oldest", false) ? "created_at ASC, priority ASC, id ASC" : "priority ASC, id ASC";
	$q = db()->prepare("SELECT jobs.*, users.email FROM jobs
		LEFT JOIN users ON jobs.user_id=users.id
		WHERE is_executed=0 ORDER BY $order_by LIMIT 20");
	$q->execute();
	while ($job = $q->fetch()) { ?>
	<tr>
		<td><?php echo number_format($job['id']); ?></td>
		<td><?php echo number_format($job['priority']); ?></td>
		<td><?php echo htmlspecialchars($job['job_type']); ?> (<?php echo htmlspecialchars($job['arg_id']); ?>)</td>
		<td class="<?php echo $job['is_test_job'] ? "yes" : "no"; ?>"><?php echo $job['is_test_job'] ? "yes" : "-"; ?></td>
		<td class="<?php echo $job['is_executing'] ? "yes" : "no"; ?>"><?php echo $job['is_executing'] ? "yes" : "-"; ?></td>
		<td><?php echo number_format($job['execution_count']); ?></td>
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
