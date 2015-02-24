<?php

/**
 * Defines routes.
 */

// load up API routes
foreach (DiscoveredComponents\Apis::getAllInstances() as $uri => $handler) {
  \Openclerk\Router::addRoutes(array(
    $handler->getEndpoint() => $handler,
  ));
}

\Openclerk\Router::addRoutes(array(
  "admin/:key" => "../pages/admin_:key.php",
  "help/:key" => "../pages/kb.php?q=:key",
  ":anything" => "../pages/:anything.php",
));
