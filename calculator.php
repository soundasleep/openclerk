<?php

/**
 * A simple calculator to calculate the value of one currency in another currency.
 */

require(__DIR__ . "/inc/global.php");

require(__DIR__ . "/layout/templates.php");
page_header("Cryptocurrency Calculator", "page_calculator", array('jquery' => true, 'js' => 'calculator'));

require_template("calculator");

require(__DIR__ . "/_calculator.php");

require_template("calculator_footer");

page_footer();
