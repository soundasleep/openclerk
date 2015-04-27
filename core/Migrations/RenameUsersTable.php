<?php

namespace Core\Migrations;

/**
 * This renames 'users' table to 'user_properties' (issue #266).
 */
class RenameUsersTable extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $q = $db->prepare("RENAME TABLE users TO user_properties");
    return $q->execute();
  }

}
