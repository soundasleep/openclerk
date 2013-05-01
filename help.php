<?php

require("inc/global.php");

require("layout/templates.php");
page_header("Help", "page_help", array('common_js' => true, 'jquery' => true, 'js' => 'help'));

require_template("help");

page_footer();
