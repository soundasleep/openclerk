<?php

namespace Core\Migrations;

/**
 * Copies identities from `user_properties` to `user_passwords` (issue #266).
 */
class CopyPasswords extends \Db\Migration {

  function getParents() {
    return array_merge(parent::getParents(), array(
        new CopyUsers(),
        new RenameUsersTable(),
        new \Users\Migrations\User(),
        new \Users\Migrations\UserPassword(),
      ));
  }

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $q = $db->prepare("INSERT INTO user_passwords (id, user_id, created_at, password_hash) (SELECT id, id, created_at, password_hash FROM user_properties WHERE NOT(ISNULL(password_hash)))");
    return $q->execute();
  }

}
