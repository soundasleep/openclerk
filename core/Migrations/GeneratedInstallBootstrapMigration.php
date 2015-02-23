<?php

namespace Core\Migrations;

use \Db\Connection;

/**
 * Allows migrations to be generated at runtime.
 * This means we don't need to create separate migrations for each new currency discovered,
 * because each currency will have a constant table structure.
 */
class GeneratedInstallBootstrapMigration extends \Db\Migration {

  function __construct($file) {
    $this->file = $file;
  }

  function getTable() {
    $bits = explode("/", str_replace("\\", "/", $this->file));
    $filename = $bits[count($bits)-1];
    $filename_bits = explode(".", $filename);
    return $filename_bits[0];
  }

  /**
   * Override the default function to check that a table exists.
   */
  function isApplied(Connection $db) {
    return $this->tableExists($db, $this->getTable());
  }

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(Connection $db) {
    if (!file_exists($this->file)) {
      throw new \Exception("Could not load file '" . $this->file . "'");
    }
    $sql = file_get_contents($this->file);
    $q = $db->prepare($sql);
    return $q->execute();
  }

  function getName() {
    return "Bootstrap_" . $this->getTable();
  }

}
