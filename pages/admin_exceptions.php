<?php

/**
 * Admin status page.
 */

require_admin();

$messages = array();
$errors = array();

page_header("Recent Exceptions", "page_admin_exceptions");

?>

<h1>Recent Exceptions</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<?php
$limit = 100;
require(__DIR__ . "/_admin_exceptions.php");
?>

<?php
page_footer();
