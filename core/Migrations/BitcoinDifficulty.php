<?php

namespace Core\Migrations;

class BitcoinDifficulty extends AbstractDifficultyMigration {

  function getCurrency() {
    return new \Core\Bitcoin();
  }

}
