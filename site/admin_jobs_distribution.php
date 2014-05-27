<?php

/**
 * Admin status page: jobs distribution
 */

require(__DIR__ . "/../inc/global.php");
require_admin();

require(__DIR__ . "/../layout/templates.php");

$messages = array();
$errors = array();

page_header("Admin: Jobs Distribution", "page_admin_jobs_distribution");

$q = db()->prepare("SELECT COUNT(*) AS c, SUM(is_error) AS errors, SUM(execution_count) AS execs, AVG(priority) AS priority, job_type FROM (SELECT * FROM jobs WHERE is_executed=1 ORDER BY id DESC LIMIT " . ((int) require_get("n", 4000)) . ") AS j GROUP BY job_type ORDER BY c DESC");
$q->execute();
$jobs = $q->fetchAll();

$total_c = 0;
foreach ($jobs as $job) {
	$total_c += $job['c'];
}

// where 0..100% = fine; 110% = good; etc
function get_error_class($n) {
	if ($n <= 1) {
		// 0%
		return "perfect";
	} else if ($n <= 1.25) {
		return "good";
	} else if ($n <= 1.5) {
		return "ok";
	} else if ($n <= 1.75) {
		return "poor";
	} else if ($n <= 2) {
		return "bad";
	} else if ($n <= 3) {
		return "broken";
	} else {
		return "dead";
	}
}

?>

<h1>Jobs Distribution</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<table class="standard">
<thead>
	<tr>
		<th>Job type</th>
		<th>Executions/Job</th>
		<th>Errors/Job</th>
		<th>Average Priority</th>
		<th>Count</th>
		<th>Percent</th>
	</tr>
</thead>
<tbody>
<?php foreach ($jobs as $job) { ?>
	<tr>
		<td><?php echo htmlspecialchars($job['job_type']); ?></td>
		<td class="number"><?php
			echo "<span class=\"status_percent " . get_error_class($job['execs'] / $job['c']) . "\">";
			echo number_format($job['execs'] / $job['c'], 2);
			echo "</span>";
		?></td>
		<td class="number"><?php
			echo "<span class=\"status_percent " . get_error_class(($job['errors'] / $job['c']) * 3) . "\">";
			echo number_format($job['errors'] / $job['c'], 2);
			echo "</span>";
		?>
		<td class="number"><?php echo number_format($job['priority'], 2); ?></td>
		<td class="number"><?php echo number_format($job['c']); ?></td>
		<td class="number"><?php echo number_format(($job['c'] / $total_c) * 100) . "%"; ?></td>
	</tr>
<?php } ?>
</tbody>
</table>

<?php
page_footer();
