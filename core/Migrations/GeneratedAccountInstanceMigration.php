<?php

namespace Core\Migrations;

use \Db\Connection;
use \Account\AccountType;

/**
 * Allows account instance migrations to be generated at runtime.
 * This means we don't need to create separate migrations for each new account type discovered,
 * because each account type will have a constant table structure.
 */
class GeneratedAccountInstanceMigration extends \Db\Migration {

  function __construct(AccountType $account) {
    $this->account = $account;
  }

  function getTable() {
    return "accounts_" . $this->account->getCode();
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
    $q = $db->prepare("CREATE TABLE " . $this->getTable() . " (
      id int not null auto_increment primary key,
      user_id int not null,
      created_at timestamp not null default current_timestamp,
      last_queue timestamp null,
      title varchar(255),

      " . $this->generateApiFields() . "

      is_disabled tinyint not null default 0,
      failures tinyint not null default 0,
      first_failure tinyint not null default 0,
      is_disabled_manually tinyint not null default 0,

      INDEX(user_id),
      INDEX(last_queue),
      INDEX(is_disabled),
      INDEX(is_disabled_manually)
    );");
    return $q->execute();
  }

  function getName() {
    return parent::getName() . "_" . $this->account->getCode();
  }

  function generateApiFields() {
    $result = "";
    foreach ($this->account->getFields() as $key => $field) {
      $type = "varchar(255)";
      if (isset($field['type'])) {
        switch ($field['type']) {
          case "datetime":
            $type = "timestamp";
            break;
        }
      }
      $result .= " $key $type not null,\n";
    }
    return $result;
  }

}
