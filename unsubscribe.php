<?php

require(__DIR__ . "/inc/global.php");

$email = require_get("email", false);
$hash = require_get("hash", false);

// check hash
if ($hash !== md5(get_site_config('unsubscribe_salt') . $email))
	throw new Exception("Invalid hash - please recheck the link in your e-mail.");

$query = db()->prepare("UPDATE users SET email=NULL,updated_at=NOW() where email=?");
$query->execute(array($email));

require(__DIR__ . "/layout/templates.php");
page_header("Unsubscribe", "page_unsubscribe");

?>
<h1>Unsubscribe</h1>

<p class="success">
Your e-mail address, <a href="mailto:<?php echo htmlspecialchars($email); ?>"><?php echo htmlspecialchars($email); ?></a>, has
been completely removed from this site, and you will no longer receive any information or notifications via e-mail.
</p>

<p>
If you have accidentally removed your e-mail from your account, you will need to login and
<a href="<?php echo htmlspecialchars(url_for('user')); ?>">add your e-mail address back to your profile</a>, in order to
resume e-mail notifications.
</p>

<?php
page_footer();