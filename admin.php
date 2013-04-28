<?php

/**
 * Admin status page.
 */

require("inc/global.php");
require_admin();

require("layout/templates.php");

$messages = array();
$errors = array();

page_header("Status", "page_admin");

?>

<h1>Site Status</h1>

<ul>
	<li><a href="<?php echo htmlspecialchars(url_for("admin_jobs")); ?>">Job status</a></li>
</ul>

<p>
TODO
</p>

<?php
page_footer();
