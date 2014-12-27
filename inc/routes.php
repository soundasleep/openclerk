<?php

/**
 * Defines routes.
 */

\Openclerk\Router::addRoutes(array(
  "admin/:key" => "../pages/admin_:key.php",
  ":anything" => "../pages/:anything.php",
));
