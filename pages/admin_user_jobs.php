<?php

/**
 * Admin page for listing recent user jobs (#73).
 */

require_admin();

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../layout/graphs.php");

$messages = array();
$errors = array();

page_header("Admin: Users Recent Jobs", "page_admin_user_jobs", array('js' => array('accounts')));

$user = get_user(require_get("id"));

// get recent jobs
$q = db()->prepare("SELECT * FROM jobs WHERE user_id=? ORDER BY id DESC LIMIT 500");
$q->execute(array($user['id']));
$jobs = $q->fetchAll();

?>

<h1>Recent User Jobs: #<?php echo number_format($user['id']); ?></h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin_user_list')); ?>">&lt; Back to User List</a></p>

<?php require(__DIR__ . "/_sort_buttons.php"); ?>

<table class="standard standard_account_list">
<thead>
  <tr>
    <th class="default_sort_down">Job ID</th>
    <th>Type</th>
    <th>Argument</th>
    <th>Created at</th>
    <th>Executed at</th>
    <th>Executed</th>
    <th>Error</th>
    <th></th>
  </tr>
</thead>
<tbody>
<?php
  $count = 0;
  foreach ($jobs as $job) {
    echo "<tr>\n";
    echo "<td class=\"number\">" . number_format($job['id']) . "</td>\n";
    echo "<td>" . htmlspecialchars($job['job_type']) . "</td>\n";
    echo "<td class=\"number\">" . number_format($job['arg_id']) . "</td>\n";
    echo "<td>" . recent_format_html($job['created_at']) . "</td>\n";
    echo "<td>" . recent_format_html($job['executed_at']) . "</td>\n";
    echo "<td class=\"" . ($job['is_executed'] ? 'yes' : 'no') . "\">-</td>\n";
    echo "<td class=\"" . ($job['is_error'] ? 'error' : 'no') . "\">-</td>\n";
    echo "<td>";
    {
      echo "<form action=\"" . htmlspecialchars(url_for('admin_run_job')) . "\" method=\"get\">";
      echo "<input type=\"hidden\" name=\"job_id\" value=\"" . htmlspecialchars($job['id']) . "\">";
      echo "<input type=\"hidden\" name=\"force\" value=\"1\">";
      echo "<input type=\"submit\" value=\"Run\">";
      echo "</form>";
    }
    echo "</td>\n";
    echo "</tr>\n\n";
  }
?>
</tbody>
</table>

<?php
page_footer();
