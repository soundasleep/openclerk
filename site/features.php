<?php

require(__DIR__ . "/../inc/global.php");

require(__DIR__ . "/../layout/templates.php");
page_header("Features", "page_features", array('class' => 'fancy_page'));

require_template("features");

page_footer();
