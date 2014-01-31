<?php

/**
 * Admin status page.
 */

require(__DIR__ . "/inc/global.php");
require_admin();

require(__DIR__ . "/layout/templates.php");
require(__DIR__ . "/layout/graphs.php");

$messages = array();
$errors = array();

page_header("Status", "page_admin", array('common_js' => true, 'jquery' => true, 'jsapi' => true));

?>

<h1>Site Status</h1>

<ul>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_jobs")); ?>">Job status</a> - <a href="<?php echo htmlspecialchars(url_for("admin_jobs", array('oldest' => true))); ?>">oldest jobs</a> - <a href="<?php echo htmlspecialchars(url_for("admin_jobs_distribution")); ?>">jobs distribution</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_email")); ?>">Send test e-mail</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_financial")); ?>">Financial report</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_users")); ?>">Users report</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_subscribe")); ?>">Pending subscription requests</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_user_list")); ?>">Users administration</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_accounts")); ?>">Accounts Status</a></li>
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
		'users' => array('title' => 'Users', 'extra' => array('is_disabled=1' => 'Disabled')),
		'addresses' => array('title' => 'Addresses'),
		'jobs' => array('title' => 'Jobs', 'extra' => array('is_executed=0' => 'Pending')),
		'outstanding_premiums' => array('title' => 'Premiums', 'extra' => array('is_paid=1' => 'Paid')),
		'uncaught_exceptions' => array('title' => 'Uncaught exceptions'),
		'ticker' => array('title' => 'Ticker instances'),
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
		echo "<td class=\"number\">" . number_format($c['c']) . " (" . get_currency_abbr($c['currency']) . ")</td>";
	}
	echo "</tr>";
	echo "<tr>";
	echo "<th>Job queue delay</th>";
	$q = db()->prepare("SELECT jobs.* FROM jobs JOIN users ON jobs.user_id=users.id WHERE users.is_premium=0 AND is_executed=0 ORDER BY jobs.created_at ASC LIMIT 1");
	$q->execute();
	$c = $q->fetch();
	echo "<td class=\"number\">" . recent_format_html($c['created_at']) . ": " . number_format($c['id']) . " (free)</td>";
        $q = db()->prepare("SELECT jobs.* FROM jobs JOIN users ON jobs.user_id=users.id WHERE users.is_premium=1 AND is_executed=0 ORDER BY jobs.created_at ASC LIMIT 1");
        $q->execute();
        $c = $q->fetch();
        echo "<td class=\"number\">" . recent_format_html($c['created_at']) . ": " . number_format($c['id']) . " (premium)</td>";
	echo "</tr>";
?>
</tbody>
</table>

<h2><a href="<?php echo htmlspecialchars(url_for('admin_exceptions')); ?>">Recent Exceptions</a></h2>

<?php
$limit = 20;
require(__DIR__ . "/_admin_exceptions.php");
?>

<h2>Site Statistics</h2>

<?php

$graph = array(
	'graph_type' => 'statistics_queue',
	'width' => 6,
	'height' => 4,
	'page_order' => 0,
	// 'days' => 30,
	'delta' => '',
	'id' => 0,
	'public' => true,
);

?>

	<div class="graph_collection" style="float: right; width: 60%;">
	<div class="graph graph_<?php echo htmlspecialchars($graph['graph_type']); ?>" id="graph<?php echo htmlspecialchars($graph['id']); ?>">
		<?php render_graph($graph, true /* is not actually public, but the graph logic will take care of this */); ?>
	</div>
	</div>

<table class="standard">
<thead>
	<tr>
		<th>Key</th>
		<th>Value</th>
	</tr>
</thead>
<tbody>
<?php
	$q = db()->prepare("SELECT * FROM site_statistics WHERE is_recent=1");
	$q->execute();
	$stats = $q->fetch();
	foreach ($stats as $key => $value) {
		if (is_numeric($key)) continue;
		$dp = 0;
		$suffix = "";
		$status = "";
		if (substr($key, -strlen("disk_free_space")) == "disk_free_space") {
			$suffix = " GB";
			$value = $value / pow(1024, 3);
			if ($value < 0.5) {
				$status = "broken";
			} if ($value < 1) {
				$status = "bad";
			} else if ($value < 5) {
				$status = "poor";
			} else if ($value < 10) {
				$status = "good";
			} else {
				$status = "perfect";
			}
			$dp = 3;
		}
		if (strpos($key, "system_load") !== false) {
			$dp = 2;
			if ($value > 2) {
				$status = "bad";
			} else if ($value > 1) {
				$status = "poor";
			} else if ($value > 0.5) {
				$status = "ok";
			} else if ($value > 0.25) {
				$status = "good";
			} else {
				$status = "perfect";
			}
		}
		if ($key == "pending_subscriptions") {
			if ($value >= 90) {
				$status = "bad";
			}
		}
		?>
	<tr>
		<th><?php echo htmlspecialchars($key); ?></th>
		<td class="number"><span class="<?php echo $status ? "status_percent " . $status : ""; ?>"><?php echo $key == "created_at" ? recent_format_html($value) : number_format($value, $dp); echo $suffix; ?></span></td>
	</tr>
	<?php } ?>
	<tr>
		<th>mysql_qps (average)</th>
		<td class="number"><?php echo number_format($stats['mysql_questions'] / $stats['mysql_uptime'], 2); ?></td>
	</tr>
	<?php
?>
</tbody>
</table>

<?php
page_footer();
