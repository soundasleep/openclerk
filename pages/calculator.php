<?php

/**
 * A simple calculator to calculate the value of one currency in another currency.
 */

page_header(t("Cryptocurrency Calculator"), "page_calculator", array('js' => 'calculator'));

require_template("calculator");

require(__DIR__ . "/_calculator.php");

require_template("calculator_footer");

page_footer();
