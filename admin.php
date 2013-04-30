<?php

/**
 * Admin status page.
 */

require("inc/global.php");
require_admin();

require("layout/templates.php");

$messages = array();
$errors = array();

page_header("Status", "page_admin");

?>

<h1>Site Status</h1>

<ul>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_jobs")); ?>">Job status</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_email")); ?>">Send test e-mail</a></li>
</ul>

<p>
TODO
</p>

<h2>Recent Exceptions</h2>

<table class="standard">
<thead>
	<tr>
		<th>ID</th>
		<th>Date</th>
		<th>Type</th>
		<th>Message</th>
		<th>Source</th>
		<th>Job ID</th>
	</tr>
</thead>
<tbody>
<?php
	$q = db()->prepare("SELECT uncaught_exceptions.*, jobs.job_type FROM uncaught_exceptions
		LEFT JOIN jobs ON uncaught_exceptions.job_id=jobs.id
		ORDER BY id DESC LIMIT 20");
	$q->execute();
	while ($e = $q->fetch()) {
		$path = str_replace("\\", "/", $e['filename']); ?>
	<tr>
		<td><?php echo number_format($e['id']); ?></td>
		<td><?php echo recent_format_html($e['created_at']); ?></td>
		<td><?php echo htmlspecialchars($e['class_name']); ?></td>
		<td><?php echo htmlspecialchars($e['message']); ?></td>
		<td><?php echo htmlspecialchars(substr($path, strrpos($path, '/') + 1) . ":" . $e['line_number']); ?></td>
		<td><?php echo htmlspecialchars($e['job_id']); echo $e['job_type'] ? (": " . htmlspecialchars($e['job_type'])) : ""; ?></td>
	</tr>
	<?php }
?>
</tbody>
</table>


<?php
page_footer();
