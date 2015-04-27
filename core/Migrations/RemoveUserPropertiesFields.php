<?php

namespace Core\Migrations;

/**
 * Removes unusued fields on `user_properties`.
 */
class RemoveUserPropertiesFields extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $q = $db->prepare("ALTER TABLE user_properties DROP password_hash, DROP password_last_changed, DROP last_password_reset");
    return $q->execute();
  }

}
