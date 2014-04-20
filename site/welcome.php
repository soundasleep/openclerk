<?php

/**
 * Signup welcome page, offering premium signups.
 */

require(__DIR__ . "/../inc/global.php");

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../graphs/types.php");

$user = get_user(user_id());
require_user($user);

$messages = array();
$errors = array();

page_header("Welcome to " . get_site_config('site_name'), "page_welcome");

?>

<h1>Select account type</h1>

<p>
Welcome to <?php echo htmlspecialchars(get_site_config('site_name')); ?>! To finish creating your account, please select your type of account below.
</p>

<?php
$welcome = true;
require(__DIR__ . "/_premium_features.php");
?>

<p>
	By purchasing a premium account, you will be able to track more addresses and accounts, and
	access advanced technical indicators for all graph types. Your accounts will also be refreshed
	much more frequently, and your jobs will have priority over free users, increasing the
	accuracy and frequency of your reports and summaries.
</p>

<p>
	Your account details and premium purchases can be managed at any time by visiting your <a href="<?php echo htmlspecialchars(url_for('user')); ?>">user account</a>.
</p>

<?php
page_footer();
