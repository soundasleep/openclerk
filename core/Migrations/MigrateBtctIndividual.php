<?php

namespace Core\Migrations;

/**
 * Updates individual securities data for `accounts_individual_btct`
 * to use their security name rather than ID.
 */
class MigrateBtctIndividual extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $q = $db->prepare("ALTER TABLE accounts_individual_btct ADD security VARCHAR(64) NOT NULL");
    if (!$q->execute()) {
      throw new \Exception("Could not add security row to table");
    }

    // this is easier than writing sql
    $q = $db->prepare("SELECT * FROM securities_btct");
    if (!$q->execute()) {
      throw new \Exception("Could not select from securities");
    }
    $securities = $q->fetchAll();

    foreach ($securities as $security) {
      $q = $db->prepare("UPDATE accounts_individual_btct SET security=:security WHERE security_id=:id");
      if (!$q->execute(array("security" => $security['name'], 'id' => $security['id']))) {
        throw new \Exception("Could not migrate old securities for security " . $security['name']);
      }
    }

    // and then remove the security_id field
    $q = $db->prepare("ALTER TABLE accounts_individual_btct DROP security_id");
    if (!$q->execute()) {
      throw new \Exception("Could not remove security_id row from table");
    }

    return true;
  }

}
