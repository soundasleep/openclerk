<?php

namespace Core\Migrations;

/**
 * This wraps up all the migrations used to migrate the old database
 * (managed by database.sql) to the new database (managed by
 * openclerk/db + migrations identified by component-discovery).
 */
class UseDbMigration extends \Db\Migration {

  function getParents() {
    return array(
      new \Db\BaseMigration(),
      new RemovePerformanceMetrics(),
      new RemovePerformanceMetrics2(),
    );
  }

  function getName() {
    return "UseDbMigration_" . $this->generateHash();
  }

  function generateHash() {
    $result = array();
    foreach ($this->getParents() as $parent) {
      $result[] = $parent->getName();
    }
    return md5(implode(",", $result));
  }

}
