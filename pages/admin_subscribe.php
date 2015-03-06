<?php

/**
 * Admin status page: jobs
 */

require_admin();

require(__DIR__ . "/../layout/templates.php");

$messages = array();
$errors = array();

if (require_post("submit", false)) {
  $q = db()->prepare("DELETE FROM pending_subscriptions");
  $q->execute(array());
  $messages[] = "Deleted all pending subscription and unsubscription requests.";
}

page_header("Admin: Pending Subscription Requests", "page_admin_subscribe");

?>

<h1>Pending Subscription Requests</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<h2>Pending Subscriptions</h2>

<textarea rows="10" cols="60"><?php
$q = db()->prepare("SELECT users.email FROM pending_subscriptions JOIN users ON pending_subscriptions.user_id=users.id AND is_subscribe=1");
$q->execute();
while ($email = $q->fetch()) {
  echo htmlspecialchars($email['email']) . ", ";
}
?>
</textarea>

<h2>Pending Unsubscriptions</h2>

<textarea rows="10" cols="60"><?php
$q = db()->prepare("SELECT users.email FROM pending_subscriptions JOIN users ON pending_subscriptions.user_id=users.id AND is_subscribe=0");
$q->execute();
while ($email = $q->fetch()) {
  echo htmlspecialchars($email['email']) . "\n";
}
?>
</textarea>

<hr>

<form action="<?php echo htmlspecialchars(url_for("admin_subscribe")); ?>" method="post">
<input type="submit" value="Remove all pending subscriptions">
<input type="hidden" name="submit" value="1">
</form>

<?php
page_footer();
