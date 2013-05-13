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

<table class="standard">
<thead>
	<tr>
		<th></th>
		<th>Total</th>
		<th>Last week</th>
		<th>Last day</th>
		<th>Last hour</th>
	</tr>
</thead>
<tbody>
<?php
	$summary = array(
		'users' => array('title' => 'Users'),
		'addresses' => array('title' => 'Addresses'),
		'jobs' => array('title' => 'Jobs', 'extra' => array('is_executed=0' => 'Pending')),
		'outstanding_premiums' => array('title' => 'Premiums', 'extra' => array('is_paid=1' => 'Paid')),
		'uncaught_exceptions' => array('title' => 'Uncaught exceptions'),
		'summaries' => array('title' => 'Summaries'),
		'graphs' => array('title' => 'Graphs'),
		'graph_pages' => array('title' => 'Graph pages'),
		'ticker' => array('title' => 'Ticker instances'),
		'balances' => array('title' => 'Balance instances'),
		'summary_instances' => array('title' => 'Summary instances'),
	);
	foreach ($summary as $key => $data) {
		echo "<tr>";
		echo "<th>" . $data['title'];
		if (isset($data['extra'])) {
			foreach ($data['extra'] as $extra_key => $extra_title) {
				echo " ($extra_title)";
			}
		}
		echo "</th>\n";
		$parts = array('1', 'date_add(created_at, interval 7 day) >= now()', 'date_add(created_at, interval 1 day) >= now()', 'date_add(created_at, interval 1 hour) >= now()');
		foreach ($parts as $query) {
			$q = db()->prepare("SELECT COUNT(*) AS c FROM $key WHERE $query");
			$q->execute();
			$c = $q->fetch();
			echo "<td class=\"number\">" . number_format($c['c']);

			if (isset($data['extra'])) {
				foreach ($data['extra'] as $extra_key => $extra_title) {
					$q = db()->prepare("SELECT COUNT(*) AS c FROM $key WHERE $query AND $extra_key");
					$q->execute();
					$c = $q->fetch();
					echo " (" . number_format($c['c']) . ")";
				}
			}

			echo "</td>\n";
		}
		echo "</tr>";
	}
	echo "<tr>";
	echo "<th>Unused Premium Addresses</th>";
	$q = db()->prepare("SELECT currency, COUNT(*) AS c FROM premium_addresses WHERE is_used=0 GROUP BY currency");
	$q->execute();
	while ($c = $q->fetch()) {
		echo "<td class=\"number\">" . number_format($c['c']) . " (" . strtoupper($c['currency']) . ")</td>";
	}
	echo "</tr>";
?>
</tbody>
</table>

<h2>Recent Exceptions</h2>

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
		ORDER BY id DESC LIMIT 20");
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
		<td><?php if ($e['job_id']) { ?><a href="<?php echo htmlspecialchars(url_for('admin_run_job', array('job_id' => $e['job_id']))); ?>">Run again</a><?php } ?></td>
	</tr>
	<?php }
?>
</tbody>
</table>


<?php
page_footer();
