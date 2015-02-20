<?php

namespace Core\Migrations;

/**
 * This renames 'litecoin_blocks' external API status to 'blockcount_ltc' etc,
 * as of version 0.30.3.
 */
class ExternalAPIsMigrationBlocks extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $mapping = array(
      'litecoin_block' => 'blockcount_ltc',
      'feathercoin_block' => 'blockcount_ftc',
      'ppcoin_block' => 'blockcount_ppc',
      'novacoin_block' => 'blockcount_nvc',
      'primecoin_block' => 'blockcount_xpm',
      'terracoin_block' => 'blockcount_trc',
      'dogecoin_block' => 'blockcount_dog',
      'megacoin_block' => 'blockcount_mec',
      'ripple_block' => 'blockcount_xrp',
      'namecoin_block' => 'blockcount_nmc',
      'digitalcoin_block' => 'blockcount_dgc',
      'worldcoin_block' => 'blockcount_wdc',
      'ixcoin_block' => 'blockcount_ixc',
      'vertcoin_block' => 'blockcount_vtc',
      'netcoin_block' => 'blockcount_net',
      'hobonickels_block' => 'blockcount_hbn',
      'blackcoin_block' => 'blockcount_bc1',
      'darkcoin_block' => 'blockcount_drk',
      'vericoin_block' => 'blockcount_vrc',
      'nxt_block' => 'blockcount_nxt',
      'reddcoin_block' => 'blockcount_rdd',
      'viacoin_block' => 'blockcount_via',
      'nubits_block' => 'blockcount_nbt',
      'nushares_block' => 'blockcount_nsr',
    );

    foreach ($mapping as $from => $to) {
      $q = $db->prepare("UPDATE external_status SET job_type=? WHERE job_type=?");
      if (!$q->execute(array($to, $from))) {
        throw new \Exception("Could not apply external_status migration '$from' to '$to'");
      }

      $q = $db->prepare("UPDATE external_status_types SET job_type=? WHERE job_type=?");
      if (!$q->execute(array($to, $from))) {
        throw new \Exception("Could not apply external_status_types migration '$from' to '$to'");
      }
    }

    return true;
  }

}
