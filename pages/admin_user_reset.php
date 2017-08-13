<?php

/**
 * Admin post callback for resetting user passwords.
 */

require_admin();

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../layout/graphs.php");

$messages = array();
$errors = array();

page_header("Admin: Reset User Password", "page_admin_user_reset");
$id = require_post("id");
$user = get_user($id);
$confirm = require_post("confirm");
if (!$confirm) {
  throw new Exception("Need to confirm");
}

$new_password = sprintf("%04x%04x%04x%04x", rand(0,0xffff), rand(0,0xffff), rand(0,0xffff), rand(0,0xffff));
$hashed_password = md5(\Openclerk\Config::get("user_password_salt") . $new_password);
$q = db()->prepare("UPDATE user_passwords SET password_hash=? WHERE user_id=? LIMIT 1");
$q->execute(array($hashed_password, $id));

send_user_email($user, "admin_password_reset", array(
  "email" => $user['email'],
  "name" => $user['name'] ? $user['name'] : $email,
  "password" => $new_password,
  "ip" => user_ip(),
  "url" => absolute_url(url_for("user#user_password")),
));

?>

<h1>Reset User Password</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin_user_list')); ?>">&lt; Back to User List</a></p>

<p>
  Password for <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>"><?php echo htmlspecialchars($user['email']); ?></a>
  reset to <b><?php echo htmlspecialchars($new_password); ?></b>.
</p>

<?php
page_footer();
