<?php

require(__DIR__ . "/../inc/global.php");

require(__DIR__ . "/../layout/templates.php");
page_header(get_site_config('site_name'), "page_home", array('common_js' => true, 'jquery' => true, 'class' => 'fancy_page'));

require_template("index");

page_footer();
