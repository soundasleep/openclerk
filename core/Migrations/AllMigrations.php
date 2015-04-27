<?php

namespace Core\Migrations;

/**
 * Represents all migrations that need to be applied to Openclerk.
 */
class AllMigrations extends \Db\Migration {
  function getParents() {
    // the order is important
    return array_merge(
        array(new \Db\BaseMigration()),                         // track migrations
        array(new InstallBootstrapGenerator()),           // bootstrap up the core tables
        array(
          // migrate away from the bootstrap DB to the new DB for all components
          new ExternalAPIsMigration(),
          new ExternalAPIsMigrationBlocks(),
          new RenameBlockTables(),
          new RenameUsersTable(),
          new CopyUsers(),
          new CopyOpenIDIdentities(),
        ),
        \DiscoveredComponents\Migrations::getAllInstances()     // then apply any new discovered Migrations
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

