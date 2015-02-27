<?php
// router.php

require(__DIR__ . "/../inc/global.php");
$path = require_get("path", "index");

use \Pages\PageRenderer;
use \Openclerk\Router;

PageRenderer::addTemplatesLocation(__DIR__ . "/../templates");
PageRenderer::addTemplatesLocation(__DIR__ . "/../config/templates");

/**
 * Include compiled header code, this was a hack to work around
 * Grunt/build/deploy issues. TODO clean this up and remove this workaround
 */
function include_head_compiled() {
  echo "<!-- compiled head -->";
  $head_compiled = __DIR__ . "/head-compiled.html";
  if (file_exists($head_compiled)) {
    require($head_compiled);
  } else {
    // fix relative paths
    $input = file_get_contents(__DIR__ . "/../layout/head.html");
    $input = str_replace("src=\"", "src=\"" . htmlspecialchars(calculate_relative_path()), $input);
    echo $input;
  }
  echo "<!-- /compiled head -->";
}

try {
  \Openclerk\Router::process($path);
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
