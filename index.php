<?php

require("inc/global.php");

require("layout/templates.php");
page_header("Home", "page_home", false, array('common_js' => true));
$row_number = 0;

?>
<h1><?php echo htmlspecialchars(get_site_config('site_name')); ?></h1>

<p>
Welcome to <b><?php echo htmlspecialchars(get_site_config('site_name')); ?></b>.
</p>

<?php
page_footer();
