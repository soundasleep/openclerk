<?php

require(__DIR__ . "/../layout/templates.php");
page_header(t("Features"), "page_features", array('class' => 'fancy_page'));

require_template("features");

page_footer();
