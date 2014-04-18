<?php

require(__DIR__ . "/../inc/global.php");

require(__DIR__ . "/../layout/templates.php");
page_header("Screenshots", "page_screenshots", array('common_js' => true, 'jquery' => true));

require_template("screenshots");

page_footer();
