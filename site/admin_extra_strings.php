<?php

/**
 * Extra localisation strings.
 */

require(__DIR__ . "/../inc/global.php");
require_admin();

$messages = array();
$errors = array();

page_header("Extra Localisation Strings", "page_admin_extra_strings");

?>

<h1>Extra Localisation Strings</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<ul>
	<li><?php echo ht("English")); ?></li>
	<li><?php echo ht("French")); ?></li>
	<li><?php echo ht("Lolcat")); ?></li>
	<li>From account_data_grouped():
	<ul>
		<li><?php echo ht("Addresses")); ?></li>
		<li><?php echo ht("Mining pools")); ?></li>
		<li><?php echo ht("Exchanges")); ?></li>
		<li><?php echo ht("Securities")); ?></li>
		<li><?php echo ht("Individual Securities")); ?></li>
		<li><?php echo ht("Finance")); ?></li>
		<li><?php echo ht("Other")); ?></li>
		<li><?php echo ht("Hidden")); ?></li>
	</ul>
	</li>
</ul>

<?php
$limit = 100;
require(__DIR__ . "/_admin_exceptions.php");
?>

<?php
page_footer();
