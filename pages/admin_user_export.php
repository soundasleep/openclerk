<?php

/**
 * Admin post callback for generating SQL statements for getting all user account data
 * that can then be used to export.
 */

require_admin();

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../layout/graphs.php");

$messages = array();
$errors = array();

page_header("Admin: Export User", "page_admin_user_export");
$id = require_post("id");
$filename = require_post("filename", "exported.sql");
$dbname = require_post("dbname", "clerk");
$username = require_post("username", "clerk");
$password = require_post("password", "clerk");

?>

<h1>Export User</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin_user_list')); ?>">&lt; Back to User List</a></p>

<form action="<?php echo htmlspecialchars(url_for("admin_user_export")); ?>" method="post">
<table class="standard">
  <tr>
    <th><label for="name">User ID:</label></th>
    <td><input type="text" name="id" size="32" value="<?php echo htmlspecialchars($id); ?>" maxlength="255"></td>
  </tr>
  <tr>
    <th><label for="name">Filename:</label></th>
    <td><input type="text" name="filename" size="32" value="<?php echo htmlspecialchars($filename); ?>" maxlength="255"></td>
  </tr>
  <tr>
    <th><label for="name">MySQL database name:</label></th>
    <td><input type="text" name="dbname" size="32" value="<?php echo htmlspecialchars($dbname); ?>" maxlength="255"></td>
  </tr>
  <tr>
    <th><label for="name">MySQL username:</label></th>
    <td><input type="text" name="username" size="32" value="<?php echo htmlspecialchars($username); ?>" maxlength="255"></td>
  </tr>
  <tr>
    <th><label for="name">MySQL password:</label></th>
    <td><input type="text" name="password" size="32" value="<?php echo htmlspecialchars($password); ?>" maxlength="255"></td>
  </tr>
  <tr>
    <td colspan="2" class="buttons">
      <input type="submit" name="export" value="Export" class="export">
    </td>
  </tr>
</table>
</form>

<ul>
<?php
  $suffix = " >> $filename";
  $user = get_user($id);
  $common = " $dbname --user=$username --password=$password --no-create-db --no-create-info --skip-extended-insert --complete-insert ";
  echo "<li>Exporting user " . ($user ? htmlspecialchars(print_r($user, true)) : "<i>(phantom)</i>") . "</li>\n";

  echo "<li>" . htmlspecialchars("rm exported.sql") . "</li>\n";

  function export_from($table) {
    global $id, $suffix, $common;
    echo "<li>" . htmlspecialchars("mysqldump --where=\"user_id=" . $id . "\" $common $table" . $suffix) . "</li>\n";
  }

  // go through all accounts
  $already_done = array();
  foreach (account_data_grouped() as $label => $accounts) {
    foreach ($accounts as $key => $account) {
      if ($account['table'] != 'graphs' && !isset($already_done[$account['table']])) {
        export_from($account['table']);
        $already_done[$account['table']] = 1;
      }
    }
  }

  export_from('balances');
  export_from('address_balances');
  export_from('hashrates');
  export_from('securities');

  export_from('offsets');

  export_from('summary_instances');
  export_from('summaries');

  export_from('graph_data_summary');
  export_from('graph_data_balances');

  export_from('pending_subscriptions');

  // export graphs
  $q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=?");
  $q->execute(array($user['id']));
  $pages = $q->fetchAll();
  foreach ($pages as $page) {
    echo "<li>" . htmlspecialchars("mysqldump --where=\"page_id=" . $page['id'] . "\" $common graphs" . $suffix) . "</li>\n";
  }
  echo "</li>\n";

  export_from('graph_pages');
  export_from('managed_graphs');

  // finally export the user object
  echo "<li>" . htmlspecialchars("mysqldump --where=\"id=" . $id . "\" $common users" . $suffix) . "</li>\n";

?>
</ul>

<?php
page_footer();
