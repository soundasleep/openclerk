<?php

if (!isset($argv)) {
  throw new Exception("This is designed to be run from the command line only.");
}

require(__DIR__ . "/../inc/global.php");

$logger = new \Monolog\Logger("install");
$logger->pushHandler(new \Core\CliLogger());

$migrations = new \Core\Migrations\AllMigrations(db());

$db = db()->getMaster();    // force master connection

if ($migrations->hasPending($db)) {
  echo "-- Installing migrations --\n";
  $migrations->install($db, $logger);
} else {
  echo "-- No migrations need to be installed --\n";
}
