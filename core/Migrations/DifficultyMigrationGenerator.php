<?php

namespace Core\Migrations;

use \Db\Connection;

/**
 * Allows migrations to be generated at runtime.
 */
class DifficultyMigrationGenerator extends \Db\Migration {

  /**
   * Get all the currencies we want to create block counts for.
   */
  function getCurrencies() {
    return \DiscoveredComponents\Currencies::getDifficultyCurrencies();
  }

  function getParents() {
    return array_merge(parent::getParents(),
      $this->generateMigrations());
  }

  function getName() {
    return "DifficultyMigrationGenerator_" . implode("", $this->getCurrencies());
  }

  function generateMigrations() {
    $result = array();
    foreach ($this->getCurrencies() as $cur) {
      $result[] = new GeneratedDifficultyMigration($cur);
    }
    return $result;
  }

}
