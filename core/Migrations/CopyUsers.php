<?php

namespace Core\Migrations;

/**
 * Copies users from `user_properties` to the new `users` table (issue #266).
 */
class CopyUsers extends \Db\Migration {

  function getParents() {
    return array_merge(parent::getParents(), array(
        new \Users\Migrations\User(),
      ));
  }

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $q = $db->prepare("INSERT INTO users (id, created_at, updated_at, email, last_login) (SELECT id, created_at, updated_at, email, last_login FROM user_properties)");
    return $q->execute();
  }

}
