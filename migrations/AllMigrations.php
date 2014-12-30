<?php

namespace Migrations;

/**
 * Represents all migrations that need to be applied to Openclerk.
 */
class AllMigrations extends \Db\Migration {
  function getParents() {
    // the order is important
    return array_merge(
        array(new \Db\BaseMigration()),                          // track migrations
        array(new \Migrations\UseDbMigration()),                 // migrate the old DB to the new DB for all components
        \DiscoveredComponents\Migrations::getAllInstances(),     // then apply any new discovered ones
        array(new \Migrations\ExternalAPIsMigration(), new \Migrations\ExternalAPIsMigrationBlocks())
      );
  }

  function getName() {
    $parentStrings = array();
    foreach ($this->getParents() as $migration) {
      $parentStrings[] = $migration->getName();
    }
    return "AllMigrations_" . md5(implode(",", $parentStrings));
  }
}

