<?php
// router.php

require(__DIR__ . "/../inc/global.php");
$path = require_get("path", "index");

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
