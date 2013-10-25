<?php

require(__DIR__ . "/inc/global.php");

require(__DIR__ . "/layout/templates.php");
page_header("Home", "page_home", array('common_js' => true, 'jquery' => true, 'class' => 'fancy_page'));

require_template("index");

page_footer();
