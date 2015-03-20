<?php

/**
 * Admin post callback for deleting users from the system.
 */

require_admin();

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../layout/graphs.php");

$messages = array();
$errors = array();

page_header("Admin: Delete User", "page_admin_user_delete");
$id = require_post("id");
$confirm = require_post("confirm");
if (!$confirm) {
  throw new Exception("Need to confirm");
}

?>

<h1>Delete User</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin_user_list')); ?>">&lt; Back to User List</a></p>

<ul>
<?php

function crypto_log($message) {
  echo "<li>" . $message . "</li>\n";
}

require(__DIR__ . "/../inc/delete_user.php");
delete_user($id);

?>
</ul>

<?php
page_footer();
