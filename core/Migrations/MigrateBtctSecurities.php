<?php

namespace Core\Migrations;

/**
 * Migrates `securities_btct` to `securities_exchange_securities`
 */
class MigrateBtctSecurities extends \Db\Migration {

  function getParents() {
    return array_merge(parent::getParents(),
      array(new MigrateBtctSecuritiesTicker()),
      array(new MigrateBtctIndividual()),
      array(new SecurityExchangeSecurities()));
  }

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $q = $db->prepare("INSERT INTO security_exchange_securities (id, created_at, exchange, currency, security, is_disabled, failures, first_failure, is_disabled_manually)
      (SELECT 2000+id AS id, created_at, 'btct' AS exchange, 'btc' AS currency, name as security, 0 as is_disabled, 0 as failures, null as first_failure, 0 as is_disabled_manually
        FROM securities_btct)");
    if (!$q->execute()) {
      throw new \Exception("Could not migrate from securities_btct to security_exchange_securities");
    }

    // then delete the old table
    $q = $db->prepare("DROP TABLE securities_btct");
    if (!$q->execute()) {
      throw new \Exception("Could not drop table securities_btct");
    }

    return true;
  }

  /**
   * Override the default function to check that a table <i>does not</i> exist.
   */
  function isApplied(\Db\Connection $db) {
    return !$this->tableExists($db, "securities_btct");
  }

}
