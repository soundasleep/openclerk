<?php

namespace Core\Migrations;

/**
 * Copies identities from `openid_identities` to `user_openid_identities` (issue #266).
 */
class CopyOpenIDIdentities extends \Db\Migration {

  function getParents() {
    return array_merge(parent::getParents(), array(
        new CopyUsers(),
        new \Users\Migrations\User(),
        new \Users\Migrations\UserOpenIDIdentities(),
      ));
  }

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $q = $db->prepare("INSERT INTO user_openid_identities (id, user_id, created_at, identity) (SELECT id, user_id, created_at, url AS identity FROM openid_identities)");
    return $q->execute();
  }

  /**
   * Override the default function to check that a table doesn't exist.
   */
  function isApplied(\Db\Connection $db) {
    return !$this->tableExists($db, 'openid_identities');
  }

}
