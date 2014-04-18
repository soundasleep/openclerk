<?php

require(__DIR__ . "/../inc/global.php");

require(__DIR__ . "/../layout/templates.php");
page_header("Features", "page_features", array('common_js' => true, 'jquery' => true, 'class' => 'fancy_page'));

require_template("features");

page_footer();
