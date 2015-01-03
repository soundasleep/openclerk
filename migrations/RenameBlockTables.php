<?php

namespace Migrations;

/**
 * This renames 'litecoin_blocks' table to 'blockcount_ltc'
 * as of version 0.30.3.
 */
class RenameBlockTables extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $mapping = array(
      'litecoin_blocks' => 'blockcount_ltc',
      'feathercoin_blocks' => 'blockcount_ftc',
      'ppcoin_blocks' => 'blockcount_ppc',
      'novacoin_blocks' => 'blockcount_nvc',
      'primecoin_blocks' => 'blockcount_xpm',
      'terracoin_blocks' => 'blockcount_trc',
      'dogecoin_blocks' => 'blockcount_dog',
      'megacoin_blocks' => 'blockcount_mec',
      'ripple_blocks' => 'blockcount_xrp',
      'namecoin_blocks' => 'blockcount_nmc',
      'digitalcoin_blocks' => 'blockcount_dgc',
      'worldcoin_blocks' => 'blockcount_wdc',
      'ixcoin_blocks' => 'blockcount_ixc',
      'vertcoin_blocks' => 'blockcount_vtc',
      'netcoin_blocks' => 'blockcount_net',
      'hobonickels_blocks' => 'blockcount_hbn',
      'blackcoin_blocks' => 'blockcount_bc1',
      'darkcoin_blocks' => 'blockcount_drk',
      'vericoin_blocks' => 'blockcount_vrc',
      'nxt_blocks' => 'blockcount_nxt',
      'reddcoin_blocks' => 'blockcount_rdd',
      'viacoin_blocks' => 'blockcount_via',
      'nubits_blocks' => 'blockcount_nbt',
      'nushares_blocks' => 'blockcount_nsr',
    );

    foreach ($mapping as $from => $to) {
      if ($this->tableExists($db, $from)) {
        $q = $db->prepare("RENAME TABLE $from TO $to");
        if (!$q->execute()) {
          throw new \Exception("Could not apply external_status migration '$from' to '$to'");
        }
      }
    }

    return true;
  }

}
