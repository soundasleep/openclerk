<?php

namespace Core\Migrations;

/**
 * Removes `eligius` from the `securities_update` table;
 * no longer needed after issue #401
 */
class RemoveSecuritiesUpdateEligius extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $q = $db->prepare("DELETE FROM securities_update WHERE exchange=?");
    return $q->execute(array('eligius'));
  }

}
