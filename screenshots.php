<?php

require("inc/global.php");

require("layout/templates.php");
page_header("Screenshots", "page_screenshots", array('common_js' => true, 'jquery' => true));

require_template("screenshots");

page_footer();
