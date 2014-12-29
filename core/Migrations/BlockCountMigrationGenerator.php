<?php

namespace Core\Migrations;

use \Db\Connection;

/**
 * Allows migrations to be generated at runtime.
 */
class BlockCountMigrationGenerator extends \Db\Migration {

  /**
   * Get all the currencies we want to create block counts for.
   */
  function getCurrencies() {
    return \DiscoveredComponents\Currencies::getBlockCurrencies();
  }

  function getParents() {
    return array_merge(parent::getParents(),
      $this->generateMigrations());
  }

  function getName() {
    return "BlockCountMigrationGenerator_" . implode("", $this->getCurrencies());
  }

  function generateMigrations() {
    $result = array();
    foreach ($this->getCurrencies() as $cur) {
      $result[] = new GeneratedBlockCountMigration($cur);
    }
    return $result;
  }

}
