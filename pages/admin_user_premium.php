<?php

/**
 * Admin post callback for adding one month of premium to a user.
 */

require_admin();

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../layout/graphs.php");

$messages = array();
$errors = array();

page_header("Admin: Add User Premium", "page_admin_user_premium");
$id = require_post("id");
$user = get_user($id);
$confirm = require_post("confirm");
if (!$confirm) {
  throw new Exception("Need to confirm");
}

if ($user['is_premium']) {
  $q = db()->prepare("UPDATE user_properties SET is_premium=1,premium_expires=date_add(premium_expires, interval 1 month) where id=?");
} else {
  $q = db()->prepare("UPDATE user_properties SET is_premium=1,premium_expires=date_add(now(), interval 1 month) where id=?");
}
$q->execute(array($id));

$q = db()->prepare("SELECT * FROM user_properties WHERE id=?");
$q->execute(array($id));
$expires = $q->fetch();

send_user_email($user, "admin_add_premium", array(
  "email" => $user['email'],
  "name" => $user['name'] ? $user['name'] : $email,
  "password" => $new_password,
  "ip" => user_ip(),
  "expires" => db_date($expires['premium_expires']),
  "premium_url" => absolute_url(url_for("premium")),
  "profile_url" => absolute_url(url_for("user#user_premium")),
));

?>

<h1>Add User Premium</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin_user_list')); ?>">&lt; Back to User List</a></p>

<p>
  User <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>"><?php echo htmlspecialchars($user['email']); ?></a>
  has been credited one extra month of premium.
</p>

<?php
page_footer();
