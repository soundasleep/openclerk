<?php

require(__DIR__ . "/../layout/templates.php");
page_header(t("API"), "page_api");

require_template("api");

page_footer();
