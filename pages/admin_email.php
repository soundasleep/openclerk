<?php

/**
 * Admin status page: send test email
 */

require_admin();

require(__DIR__ . "/../layout/templates.php");

$messages = array();
$errors = array();

if (require_post("email", false)) {
	$email = require_post("email");
  $result = send_email($email, "admin_test", array(
    "date" => date('r'),
    "email" => $email,
  ));
	$messages[] = "Sent e-mail to " . htmlspecialchars($email) . ".";
}

page_header("Admin: Send Test E-mail", "page_admin_email");

?>

<h1>Send Test Email</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<form action="<?php echo htmlspecialchars(url_for('admin_email')); ?>" method="post">
<label>Send test e-mail to: <input type="text" name="email" value="<?php echo require_post("email", get_site_config('site_email')); ?>"></label>
<input type="submit">
</form>

<?php
page_footer();
