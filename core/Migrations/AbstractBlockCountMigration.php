<?php

namespace Core\Migrations;

use \Db\Connection;

abstract class AbstractBlockCountMigration extends \Db\Migration {

  /**
   * Get the currency for this migration.
   */
  abstract function getCurrency();

  function getTable() {
    return "blockcount_" . $this->getCurrency()->getCode();
  }

  /**
   * Override the default function to check that a table exists.
   */
  function isApplied(Connection $db) {
    return $this->tableExists($db, $this->getTable($db));
  }

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(Connection $db) {
    $q = $db->prepare("CREATE TABLE " . $this->getTable() . " (
      id int not null auto_increment primary key,
      created_at timestamp not null default current_timestamp,

      blockcount int not null,
      is_recent tinyint not null default 0,

      INDEX(is_recent)
    );");
    return $q->execute();
  }

}
