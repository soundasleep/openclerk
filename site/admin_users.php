<?php

/**
 * Admin status page: users report
 */

require(__DIR__ . "/../inc/global.php");
require_admin();

require(__DIR__ . "/../layout/templates.php");

$messages = array();
$errors = array();

page_header("Admin: Users Report", "page_admin_users");

?>

<h1>Users Report</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<table class="standard">
<thead>
	<tr>
		<th>Date</th>
		<th>Users</th>
	</tr>
</thead>
<tbody>
<?php
for ($i = 0; $i < 60; $i++) {
	$date = date('Y-m-d', strtotime("-$i days"));
	$q = db()->prepare("SELECT COUNT(*) AS c FROM users WHERE created_at >= ? AND created_at < date_add(?, interval 1 day)");
	$q->execute(array($date, $date));
	$c = $q->fetch();
	echo "<tr><th>" . htmlspecialchars($date) . "</th><td>" . number_format($c['c']) . "</td></tr>\n";
}
?>
</tbody>
</table>

<?php

page_footer();
