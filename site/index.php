<?php
// router.php

require(__DIR__ . "/../inc/global.php");
require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../layout/graphs.php");

use \Pages\PageRenderer;
use \Openclerk\Router;

PageRenderer::addTemplatesLocation(__DIR__ . "/../templates");
PageRenderer::addTemplatesLocation(__DIR__ . "/../config/templates");
PageRenderer::setHamlOptions(array(
  'safe_functions' => array("require_template", "link_to", "recent_format_html"),
));

$stylesheets = array(
  'styles/generated.css' . '?' . get_site_config('openclerk_version'),
  get_site_config('default_css') . '?' . get_site_config('openclerk_version'),
);
if (get_site_config('custom_css')) {
  $stylesheets[] = get_site_config('custom_css') . '?' . get_site_config('openclerk_version');
}
foreach ($stylesheets as $css) {
  PageRenderer::addStylesheet(Router::urlFor($css));
}

$javascripts = array(
  'js/jquery-1.9.1.min.js',
  'js/common.js' . '?' . get_site_config('openclerk_version'),
  'js/locale/' . get_current_locale() . '.js?' . get_site_config('openclerk_version'),
  'https://www.google.com/jsapi',
);
foreach ($javascripts as $js) {
  PageRenderer::addJavascript(Router::urlFor($js));
}

/**
 * TODO rename to require_template
 * TODO move out of the router file
 */
function require_template_new($template, $arguments = array()) {
  PageRenderer::requireTemplate($template, $arguments);
}

$path = require_get("path", "index");

try {
  // TODO define('PAGE_RENDER_START', microtime(true));

  Router::process($path);

  // TODO performance_metrics_page_end(); etc
} catch (\Openclerk\RouterException $e) {
  header("HTTP/1.0 404 Not Found");

  $errors = array();
  $errors[] = htmlspecialchars($e->getMessage());
  if (is_localhost()) {
    $errors[] = htmlspecialchars($e->getPrevious()->getMessage());
  }

  require(__DIR__ . "/404.php");
}

?>
