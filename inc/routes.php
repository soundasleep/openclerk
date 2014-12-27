<?php

/**
 * Defines routes.
 */

\Openclerk\Router::addRoutes(array(
  "admin/:key" => "../pages/admin_:key.php",
  "help/:key" => "../pages/kb.php?q=:key",
  ":anything" => "../pages/:anything.php",
));
