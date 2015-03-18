<?php

namespace Core\Migrations;

use \Db\Connection;
use \DiscoveredComponents\Accounts;

/**
 * Allows migrations to be generated at runtime.
 */
class AccountInstanceGenerator extends \Db\Migration {

  /**
   * Get all the accounts that we want to create tables for.
   */
  function getAccounts() {
    return array_diff(Accounts::getKeys(), Accounts::getDisabled());
  }

  function getParents() {
    return array_merge(parent::getParents(),
      $this->generateMigrations());
  }

  function getName() {
    return "AccountInstanceGenerator_" . md5(implode(",", $this->getAccounts()));
  }

  function generateMigrations() {
    $result = array();
    foreach ($this->getAccounts() as $account) {
      $instance = Accounts::getInstance($account);
      $result[] = new GeneratedAccountInstanceMigration($instance);
    }
    return $result;
  }

}
