<?php

/**
 * Admin page for listing and deleting users.
 */

require(__DIR__ . "/../inc/global.php");
require_admin();

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../layout/graphs.php");

$messages = array();
$errors = array();

page_header("Admin: Users", "page_admin_user_list", array('js' => array('accounts')));

$max_count = 30;
$args = array();
$search_query = "";
if (require_post("search", false)) {
	$search_query = " WHERE name LIKE :search OR email LIKE :search OR url LIKE :search";
	$args['search'] = '%' . require_post("search") . '%';
} else if (require_post("just_premium", false)) {
	$search_query = " WHERE is_premium=1";
}
$q = db()->prepare("SELECT u.*, s.c AS currencies
	FROM users AS u
		LEFT JOIN (SELECT COUNT(*) AS c, user_id FROM summaries GROUP BY user_id) AS s ON s.user_id=u.id
	$search_query
	GROUP BY u.id
	ORDER BY u.id DESC LIMIT " . ($max_count+1) . "");
$q->execute($args);
$users = $q->fetchAll();

?>

<h1>Users Report</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<form action="<?php echo htmlspecialchars(url_for('admin_user_list')); ?>" method="post">
<label>Search: <input type="text" maxlength="128" size="32" name="search" value="<?php echo htmlspecialchars(require_post("search", "")); ?>"></label>
<input type="submit" value="Search">
</form>

<form action="<?php echo htmlspecialchars(url_for('admin_user_list')); ?>" method="post">
<input type="hidden" name="search" value="">
<input type="submit" value="Reset">
</form>

<form action="<?php echo htmlspecialchars(url_for('admin_user_list')); ?>" method="post">
<input type="hidden" name="just_premium" value="1">
<input type="submit" value="Just Premium">
</form>

<p>

<span style="display:none;" id="sort_buttons_template">
<!-- heading sort buttons -->
<span class="sort_up" title="Sort ascending">Asc</span><span class="sort_down" title="Sort descending">Desc</span>
</span>

<table class="standard standard_account_list">
<thead>
	<tr>
		<th class="default_sort_down">ID</th>
		<th>Email</th>
		<th>Name</th>
		<th>Premium</th>
		<th>Premium expires</th>
		<th>Signed up</th>
		<th>Last login</th>
		<th>Currencies</th>
		<th></th>
	</tr>
</thead>
<tbody>
<?php
	$count = 0;
	foreach ($users as $user) {
		$count++;
		if ($count > $max_count) {
			echo "<tr><td colspan=\"9\"><i>(Additional results not shown here)</i></td></tr>\n";
		} else {
			$q = db()->prepare("SELECT COUNT(*) AS identity_count, url FROM openid_identities WHERE user_id=?");
			$q->execute(array($user['id']));
			$openid = $q->fetch();
			echo "<tr>\n";
			echo "<td class=\"number\">" . number_format($user['id']) . "</td>\n";
			if ($openid && $openid['identity_count']) {
				echo "<td><a href=\"" . htmlspecialchars($openid['url']) . "\">" . ($user['email'] ? htmlspecialchars($user['email']) : "<i>(no email)</i>") . "</a> " . $openid['identity_count'] . "</td>\n";
			} else {
				echo "<td>" . htmlspecialchars($user['email']) . "</a> (password)</td>\n";
			}
			echo "<td>" . htmlspecialchars($user['name']) . "</td>\n";
			echo "<td class=\"" . ($user['is_premium'] ? 'yes' : 'no') . "\">-</td>\n";
			echo "<td>" . recent_format_html($user['premium_expires'], "", "") . "</td>\n";
			echo "<td>" . recent_format_html($user['created_at']) . "</td>\n";
			echo "<td>" . recent_format_html($user['last_login']) . "</td>\n";
			echo "<td class=\"number\">" . number_format($user['currencies']) . "</td>\n";
			echo "<td>";
			{
				echo "<form action=\"" . htmlspecialchars(url_for('admin_login')) . "\" method=\"get\">";
				echo "<input type=\"hidden\" name=\"id\" value=\"" . htmlspecialchars($user['id']) . "\">";
				echo "<input type=\"submit\" value=\"Login as\">";
				echo "</form>";
			}
			{
				echo "<form action=\"" . htmlspecialchars(url_for('admin_user_export')) . "\" method=\"post\">";
				echo "<input type=\"hidden\" name=\"id\" value=\"" . htmlspecialchars($user['id']) . "\">";
				echo "<input type=\"submit\" value=\"Export\">";
				echo "</form>";
			}
			{
				echo "<form action=\"" . htmlspecialchars(url_for('admin_user_delete')) . "\" method=\"post\">";
				echo "<input type=\"hidden\" name=\"id\" value=\"" . htmlspecialchars($user['id']) . "\">";
				echo "<input type=\"hidden\" name=\"confirm\" value=\"1\">";
				echo "<input type=\"submit\" value=\"Delete\" onclick=\"return confirm('Are you sure you want to delete this user?');\">";
				echo "</form>";
			}
			echo "</td>\n";
			echo "</tr>\n\n";
		}
	}
?>
</tbody>
</table>

<?php
page_footer();
