<?php
// router.php

require(__DIR__ . "/../inc/global.php");
$path = require_get("path", "index");

try {
  \Openclerk\Router::process($path);
} catch (\Openclerk\RouterException $e) {
  header("HTTP/1.0 404 Not Found");
  echo htmlspecialchars($e->getMessage());
  if (is_localhost()) {
    echo "<br>" . htmlspecialchars($e->getPrevious()->getMessage());
  }
}

?>
