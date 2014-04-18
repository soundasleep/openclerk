<?php

require(__DIR__ . "/../inc/global.php");

require(__DIR__ . "/../layout/templates.php");
page_header("Help", "page_help", array('common_js' => true, 'jquery' => true, 'js' => 'help'));

require_template("help");

page_footer();
