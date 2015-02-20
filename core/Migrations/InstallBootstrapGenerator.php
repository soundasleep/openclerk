<?php

namespace Core\Migrations;

use \Db\Connection;

/**
 * Allows migrations to be generated at runtime.
 * Generates the necessary tables that should be created in a fresh
 * Openclerk install.
 * The files in bootstrap/*.sql should never be changed and should
 * be permanent.
 */
class InstallBootstrapGenerator extends \Db\Migration {

  /**
   * Get all the SQL files we want to create block counts for.
   */
  function getSQLs() {
    $result = array();
    if ($handle = opendir(__DIR__ . "/bootstrap/")) {
      while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && substr($entry, -4) == ".sql") {
          $result[] = __DIR__ . "/bootstrap/" . $entry;
        }
      }
      closedir($handle);
    }
    return $result;
  }

  function getParents() {
    return array_merge(parent::getParents(),
      $this->generateMigrations());
  }

  function getName() {
    return "InstallGenerator";
  }

  function generateMigrations() {
    $result = array();
    foreach ($this->getSQLs() as $sql) {
      $result[] = new GeneratedInstallBootstrapMigration($sql);
    }
    return $result;
  }

}
