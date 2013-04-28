<?php

require("inc/global.php");

require("layout/templates.php");
page_header("Home", "page_home", array('common_js' => true, 'jquery' => true));

require_template("index");

page_footer();
