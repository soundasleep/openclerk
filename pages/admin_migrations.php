<?php

/**
 * Admin status page.
 */

require_admin();

require(__DIR__ . "/../layout/templates.php");

class MyLogger extends \Db\Logger {
  function log($s) {
    echo "<li>" . htmlspecialchars($s) . "</li>";
  }
  function error($s) {
    echo "<li class=\"error\" style=\"color:red;\">" . htmlspecialchars($s) . "</li>";
  }
}

$logger = new MyLogger();

class AllMigrations extends \Db\Migration {
  function getParents() {
    // the order is important
    return array_merge(array(new Db\BaseMigration()),             // track migrations
      array(new Migrations\UseDbMigration()),      // migrate the old DB to the new DB
      DiscoveredComponents\Migrations::getAllInstances());   // then apply any new discovered ones
  }

  function getName() {
    return "AllMigrations_" . md5(implode(",", array_keys($this->getParents())));
  }
}

$migrations = new AllMigrations(db());

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
if ($migrations->hasPending(db())) {
  echo "<h2>Installing migrations</h2>";
  echo "<ul>";
  $migrations->install(db(), $logger);
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
