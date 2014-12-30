<?php

namespace Core\Migrations;

use \Db\Connection;

/**
 * Rename 'litecoin_blocks' to 'blocks_ltc'
 */
class RenameLitecoinBlocks extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(Connection $db) {
    $q = $db->prepare("RENAME TABLE litecoin_blocks TO blockcount_ltc");
    return $q->execute();
  }

}
