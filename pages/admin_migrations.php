<?php

/**
 * Admin status page for pending migrations.
 */

require_admin();

$logger = new \Monolog\Logger("admin_migrations");
$logger->pushHandler(new \Core\MyLogger());

$migrations = new \Migrations\AllMigrations(db());

page_header("Migrations", "page_migrations", array('jsapi' => true));

?>

<h1>Migrations</h1>

<h2>Migration order</h2>

<?php
function iterate_over_migrations($migration) {
  $result = array();
  foreach ($migration->getParents() as $parent) {
    // we need to install all parents before installing children
    $result = array_merge($result, iterate_over_migrations($parent));
  }
  $result[] = $migration->getName();
  $result = array_unique($result);
  return $result;
}
echo implode(" -&gt; ", iterate_over_migrations($migrations));
?>

<hr>

<?php
$db = db()->getMaster();		// force master connection

if ($migrations->hasPending($db)) {
  echo "<h2>Installing migrations</h2>";
  echo "<ul>";
  $migrations->install($db, $logger);
  echo "</ul>";
} else {
  echo "<h2>No migrations need to be installed</h2>";
}
?>

<hr>

<h2>Discovered migrations</h2>

<p><?php echo implode(", ", DiscoveredComponents\Migrations::getKeys()) . " (" . number_format(count(DiscoveredComponents\Migrations::getKeys())) . ")"; ?></p>

<?php
page_footer();
