<?php

namespace Core\Migrations;

/**
 * Remove `email` field from `user_properties`: should be obtained from `users` field instead.
 */
class RemoveEmailFromUserProperties extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $q = $db->prepare("ALTER TABLE user_properties DROP email");
    return $q->execute();
  }

}
