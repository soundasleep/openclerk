<?php

require(__DIR__ . "/../inc/global.php");

require(__DIR__ . "/../layout/templates.php");
page_header("Contact", "page_contact", array('common_js' => true, 'jquery' => true));

require_template("contact");

page_footer();
