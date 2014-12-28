<?php

/**
 * Admin status page.
 */

require_admin();

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../layout/graphs.php");

$messages = array();
$errors = array();

page_header("Status", "page_admin", array('jsapi' => true));

?>

<h1>Site Status</h1>

<?php

$graph = array(
	'graph_type' => 'admin_statistics',
	'width' => 5,
	'height' => 2,
	'page_order' => 0,
	'days' => 45,
	'delta' => '',
	'id' => 0,
	'public' => true,
);

?>

	<div class="graph_collection" style="float: right; width: 50%;">
		<?php render_graph_new($graph, true /* user_hash */); ?>
	</div>

<ul>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_jobs")); ?>">Job status</a> - <a href="<?php echo htmlspecialchars(url_for("admin_jobs", array('oldest' => true))); ?>">oldest jobs</a> - <a href="<?php echo htmlspecialchars(url_for("admin_jobs_distribution")); ?>">jobs distribution</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_email")); ?>">Send test e-mail</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_financial")); ?>">Financial report</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_users")); ?>">Users report</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_subscribe")); ?>">Pending subscription requests</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_user_list")); ?>">Users administration</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_accounts")); ?>">Accounts Status</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_metrics")); ?>">Site performance metrics</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_reported_currencies")); ?>">Exchange reported currencies</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_vote_coins")); ?>">Coin voting</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_show_explorers")); ?>">Show coin explorers</a></li>
  <li><a href="<?php echo htmlspecialchars(url_for("admin_migrations")); ?>">Install latest migrations</a></li>
  <li><a href="<?php echo htmlspecialchars(url_for("admin_currencies")); ?>">Discovered currencies</a></li>
</ul>

<h2 style="clear: both;"><a href="<?php echo htmlspecialchars(url_for('admin_exceptions')); ?>">Recent Exceptions</a></h2>

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
	'days' => 180,
	'delta' => '',
	'id' => 1,
	'public' => true,
);

?>

	<div class="graph_collection" style="float: right; width: 60%;">
		<?php render_graph_new($graph, true /* user_hash */); ?>
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
	if ($stats = $q->fetch()) {
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
				} else if ($value >= 70) {
					$status = "poor";
				} else if ($value >= 50) {
					$status = "ok";
				}
			}
			?>
			<tr>
				<th><?php echo htmlspecialchars($key); ?></th>
				<td class="number"><span class="<?php echo $status ? "status_percent " . $status : ""; ?>">
					<?php if ($key == "created_at") {
						echo recent_format_html($value);
					} else if (is_numeric($value)) {
						echo number_format($value, $dp);
					} else {
						echo htmlspecialchars($value);
					}
					echo $suffix; ?></span></td>
			</tr>
			<?php } ?>
		<tr>
			<th>mysql_qps (average)</th>
			<td class="number"><?php echo $stats['mysql_uptime'] ? number_format($stats['mysql_questions'] / $stats['mysql_uptime'], 2) : "-"; ?></td>
		</tr>
		<?php
			$value = $stats['mysql_locks_blocked'] / ($stats['mysql_locks_immediate'] + $stats['mysql_locks_blocked'] + 1 /* hack to prevent div/0 */);
			$status = "";
			if ($value > 0.1) {
				$status = "bad";
			} else if ($value > 0.05) {
				$status = "poor";
			} else if ($value > 0.025) {
				$status = "ok";
			} else if ($value > 0.01) {
				$status = "good";
			} else {
				$status = "perfect";
			}
		?>
		<tr>
			<th>locked out queries</th>
			<td class="number"><span class="<?php echo $status ? "status_percent " . $status : ""; ?>"><?php echo number_format($value, 2); ?> %</span></td>
		</tr>
		<?php
			$value = $stats['mysql_slow_queries'] / ($stats['mysql_questions'] + + 1 /* hack to prevent div/0 */);
			$status = "";
			if ($value > 0.05) {
				$status = "bad";
			} else if ($value > 0.01) {
				$status = "poor";
			} else if ($value > 0.005) {
				$status = "ok";
			} else if ($value > 0.001) {
				$status = "good";
			} else {
				$status = "perfect";
			}
		?>
		<tr>
			<th>slow queries</th>
			<td class="number"><span class="<?php echo $status ? "status_percent " . $status : ""; ?>"><?php echo number_format($value, 3); ?> %</span></td>
		</tr>
	<?php } ?>
</tbody>
</table>

<?php
page_footer();
