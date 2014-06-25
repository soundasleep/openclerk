<?php

/**
 * Admin page for displaying the status of accounts in the system, allowing us to see
 * if particular classes of accounts are failing.
 */

require(__DIR__ . "/../inc/global.php");
require_admin();

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../layout/graphs.php");

$messages = array();
$errors = array();

// enabling accounts?
if (require_post("enable", false)) {
	$exchange = require_post("enable");
	$account_data = get_account_data($exchange);

	// we re-enable ALL accounts, not just accounts belonging to active users, so that when a disabled user
	// logs back in, they automatically get their disabled accounts disabled as well
	$q = db()->prepare("SELECT t.*, users.email, users.name AS users_name, users.is_disabled AS user_is_disabled FROM " . $account_data['table'] . " t
		JOIN users ON t.user_id=users.id
		WHERE t.is_disabled=1");
	$q->execute();
	$count = 0;
	$accounts = $q->fetchAll();
	foreach ($accounts as $account) {
		// re-enable it
		$q = db()->prepare("UPDATE " . $account_data['table'] . " SET is_disabled=0 WHERE id=? AND is_disabled_manually=0");
		$q->execute(array($account['id']));

		// email the user if their account is not disabled
		if (!$account['user_is_disabled']) {
			if ($account['email']) {
				$user_temp = array('email' => $account['email'], 'name' => $account['users_name']);

				send_user_email($user_temp, "reenable", array(
					"name" => ($account['users_name'] ? $account['users_name'] : $account['email']),
					"exchange" => get_exchange_name($exchange),
					"label" => $account_data['label'],
					"labels" => $account_data['labels'],
					"title" => (isset($account['title']) && $account['title']) ? "\"" . $account['title'] . "\"" : "untitled",
					"url" => absolute_url(url_for("wizard_accounts")),
				));
				$messages[] = "Sent enabled message to " . htmlspecialchars($account['email']);

			}
		}
		$count++;
	}

	$messages[] = "Re-enabled " . plural("account", $count) . ".";
}

page_header("Admin: Accounts", "page_admin_accounts", array('js' => array('accounts')));

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
		<th>Manually disabled</th>
		<th>Last success</th>
		<th>Run job</th>
		<th>API status</th>
		<th></th>
	</tr>
</thead>
<tbody>
<?php
	foreach (account_data_grouped() as $label => $group) {
	?>
		<tr><th colspan="7"><?php echo htmlspecialchars($label); ?></th></tr>
	<?php
		foreach ($group as $exchange => $data) {
			// don't display unsafe tables
			if ($data['unsafe'] && !get_site_config('allow_unsafe')) {
				continue;
			}

			echo "<tr><td>" . htmlspecialchars(get_exchange_name($exchange) . $data['suffix']) . "</td>\n";
			if ($data['failure']) {
				$q = db()->prepare("SELECT COUNT(*) AS s, SUM(t.is_disabled) AS disabled, SUM(t.is_disabled_manually) AS manually_disabled, MAX(t.last_queue) AS lq FROM " . $data['table'] . " AS t
					LEFT JOIN users ON t.user_id=users.id
					WHERE users.is_disabled=0"
					. ((isset($data['query']) && $data['query']) ? " " . $data['query'] : ""));
			} else if ($data['job']) {
				$q = db()->prepare("SELECT COUNT(*) AS s, 0 AS disabled, 0 AS manually_disabled, MAX(t.last_queue) AS lq FROM " . $data['table'] . " AS t
					LEFT JOIN users ON t.user_id=users.id
					WHERE users.is_disabled=0"
					. ((isset($data['query']) && $data['query']) ? " " . $data['query'] : ""));
			} else {
				$q = db()->prepare("SELECT COUNT(*) AS s, 0 AS disabled, 0 AS manually_disabled, NULL AS lq FROM " . $data['table'] . " AS t
					WHERE 1"
					. ((isset($data['query']) && $data['query']) ? " " . $data['query'] : ""));
			}
			$q->execute();
			$summary = $q->fetch();

			// executing this in two queries is faster than going ORDER BY is_error DESC
			$q = db()->prepare("SELECT * FROM jobs WHERE job_type=? AND is_test_job=0 AND is_error=1 LIMIT 1");
			$q->execute(array($exchange));
			$job = $q->fetch();
			if (!$job) {
				// if there are no failing jobs, just select any one
				$q = db()->prepare("SELECT * FROM jobs WHERE job_type=? AND is_test_job=0 LIMIT 1");
				$q->execute(array($exchange));
				$job = $q->fetch();
			}

			echo "<td class=\"number\">" . number_format($summary['s']) . "</td>\n";

			if ($data['disabled']) {
				echo "<td class=\"disabled\">disabled</a>\n";
			} else {
				if ($summary['s'] == 0) {
					echo "<td class=\"disabled\">no data</a>\n";
				} else {
					$pct = 1 - (($summary['disabled'] - $summary['manually_disabled']) / ($summary['s'] - $summary['manually_disabled']));
					echo "<td class=\"status_percent " . get_error_class($pct) . "\">" . number_format_autoprecision($pct * 100, 2) . " %</td>\n";
				}
			}

			echo "<td class=\"number\">" . number_format($summary['manually_disabled']) . "</td>\n";

			echo "<td>" . recent_format_html($summary['lq']) . "</td>\n";
			?>
			<td class="number">
				<?php if ($job) { ?>
				<a href="<?php echo htmlspecialchars(url_for('admin_run_job', array('job_id' => $job['id'], 'force' => true))); ?>"><?php echo number_format($job['id']); ?></a>
				<?php } ?>
			</td>
			<?php
			echo "<td><a href=\"" . htmlspecialchars(url_for('external_historical', array('type' => $exchange))) . "\">Graph</a></td>";
			echo "<td class=\"buttons\">";
			if ($data['failure'] && $summary['disabled'] > 0) {
				echo "<form action=\"" . htmlspecialchars(url_for('admin_accounts')) . "\" method=\"post\">";
				echo "<input type=\"hidden\" name=\"enable\" value=\"" . htmlspecialchars($exchange) . "\">";
				echo "<input type=\"submit\" value=\"Enable all\" onclick=\"return confirm('Are you sure you want to re-enable all failed accounts?');\">";
				echo "</form>";

				echo "<form action=\"" . htmlspecialchars(url_for('admin_accounts_message')) . "\" method=\"post\">";
				echo "<input type=\"hidden\" name=\"exchange\" value=\"" . htmlspecialchars($exchange) . "\">";
				echo "<input type=\"submit\" value=\"Message failed\">";
				echo "</form>";
			}
			echo "</td>\n";
			echo "</tr>";
		}
	}
?>
</tbody>
</table>

<?php
page_footer();
