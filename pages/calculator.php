<?php

/**
 * A simple calculator to calculate the value of one currency in another currency.
 */

use \Pages\PageRenderer;

define('__TEMPLATE_DIR__', __DIR__);

PageRenderer::header(array("title" => t("Cryptocurrency Calculator"), "id" => "page_calculator", "js" => "calculator"));
PageRenderer::requireTemplate("calculator");
PageRenderer::footer();
