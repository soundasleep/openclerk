<?php

/**
 * Admin page for displaying the status of accounts in the system, allowing us to see
 * if particular classes of accounts are failing.
 */

require(__DIR__ . "/inc/global.php");
require_admin();

require(__DIR__ . "/layout/templates.php");
require(__DIR__ . "/layout/graphs.php");

$messages = array();
$errors = array();

page_header("Admin: Accounts", "page_admin_accounts", array('jquery' => true, 'js' => array('common', 'accounts')));

// where 0% = bad; 100% = perfect; etc
function get_error_class($n) {
	if ($n >= 0.9) {
		// 0%
		return "perfect";
	} else if ($n >= 0.75) {
		return "good";
	} else if ($n >= 0.5) {
		return "ok";
	} else if ($n >= 0.2) {
		return "broken";
	} else {
		return "dead";
	}
}

?>

<h1>Accounts Report</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<table class="standard standard_account_list">
<thead>
	<tr>
		<th class="default_sort_down">Account Type</th>
		<th>Active Accounts</th>
		<th>Failed</th>
		<th>Last success</th>
		<th>Run job</th>
		<th>API status</th>
	</tr>
</thead>
<tbody>
<?php
	foreach (account_data_grouped() as $label => $group) {
	?>
		<tr><th colspan="6"><?php echo htmlspecialchars($label); ?></th></tr>
	<?php
		foreach ($group as $exchange => $data) {
			// don't display unsafe tables
			if ($data['unsafe'] && !get_site_config('allow_unsafe')) {
				continue;
			}

			echo "<tr><td>" . htmlspecialchars(get_exchange_name($exchange)) . "</td>\n";
			if ($data['failure']) {
				$q = db()->prepare("SELECT COUNT(*) AS s, SUM(t.is_disabled) AS disabled, MAX(t.last_queue) AS lq FROM " . $data['table'] . " AS t
					LEFT JOIN users ON t.user_id=users.id
					WHERE users.is_disabled=0");
			} else if ($data['job']) {
				$q = db()->prepare("SELECT COUNT(*) AS s, 0 AS disabled, MAX(t.last_queue) AS lq FROM " . $data['table'] . " AS t
					LEFT JOIN users ON t.user_id=users.id
					WHERE users.is_disabled=0");
			} else {
				$q = db()->prepare("SELECT COUNT(*) AS s, 0 AS disabled, NULL AS lq FROM " . $data['table'] . " AS t");
			}
			$q->execute();
			$summary = $q->fetch();

			$q = db()->prepare("SELECT * FROM jobs WHERE job_type=? AND is_test_job=0 LIMIT 1");
			$q->execute(array($exchange));
			$job = $q->fetch();

			echo "<td class=\"number\">" . number_format($summary['s']) . "</td>\n";

			if ($data['disabled']) {
				echo "<td class=\"disabled\">disabled</a>\n";
			} else {
				if ($summary['s'] == 0) {
					echo "<td class=\"disabled\">no data</a>\n";
				} else {
					$pct = 1 - (number_format($summary['disabled']) / number_format($summary['s']));
					echo "<td class=\"status_percent " . get_error_class($pct) . "\">" . number_format_autoprecision($pct * 100, 2) . " %</td>\n";
				}
			}

			echo "<td>" . recent_format_html($summary['lq']) . "</td>\n";
			?>
			<td class="number">
				<?php if ($job) { ?>
				<a href="<?php echo htmlspecialchars(url_for('admin_run_job', array('job_id' => $job['id'], 'force' => true))); ?>"><?php echo number_format($job['id']); ?></a>
				<?php } ?>
			</td>
			<?php
			echo "<td><a href=\"" . htmlspecialchars(url_for('external_historical', array('type' => $exchange))) . "\">Graph</a></td>";
			echo "</tr>";
		}
	}
?>
</tbody>
</table>

<?php
page_footer();
