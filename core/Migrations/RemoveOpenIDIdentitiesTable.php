<?php

namespace Core\Migrations;

/**
 * Removes the `openid_identities` table.
 */
class RemoveOpenIDIdentitiesTable extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $q = $db->prepare("DROP TABLE openid_identities");
    return $q->execute();
  }

  /**
   * Override the default function to check that a table exists.
   */
  function isApplied(Connection $db) {
    return $this->tableExists($db, 'openid_identities');
  }

}
