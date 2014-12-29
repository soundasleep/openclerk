<?php

namespace Core\Migrations;

class BitcoinBlockCount extends AbstractBlockCountMigration {

  function getCurrency() {
    return new \Cryptocurrency\Bitcoin();
  }

}
