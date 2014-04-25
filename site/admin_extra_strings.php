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
	<li><?php echo htmlspecialchars(t("English")); ?></li>
	<li><?php echo htmlspecialchars(t("French")); ?></li>
	<li><?php echo htmlspecialchars(t("Lolcat")); ?></li>
</ul>

<?php
$limit = 100;
require(__DIR__ . "/_admin_exceptions.php");
?>

<?php
page_footer();
