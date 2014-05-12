<?php

require(__DIR__ . "/../inc/global.php");

require(__DIR__ . "/../layout/templates.php");
page_header(t("Screenshots"), "page_screenshots");

require_template("screenshots");

page_footer();
