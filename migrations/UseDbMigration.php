<?php

namespace Migrations;

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
    );
  }

  function getName() {
    return "UseDbMigration_" . md5(implode(",", array_keys($this->getParents())));
  }

}
