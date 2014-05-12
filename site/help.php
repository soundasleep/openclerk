<?php

require(__DIR__ . "/../inc/global.php");

require(__DIR__ . "/../layout/templates.php");
page_header(t("Help"), "page_help", array('js' => 'help'));

require_template("help");

page_footer();
