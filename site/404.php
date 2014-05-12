<?php

require(__DIR__ . "/../inc/global.php");

require(__DIR__ . "/../layout/templates.php");
page_header(t("Not Found"), "page_404");

?>
<h1><?php echo ht("Not Found"); ?></h1>

<p><?php echo ht("That resource could not be found."); ?> :(</p>

<?php
page_footer();
