<?php

namespace Migrations;

/**
 * This renames 'blockchain' external API status to 'address_btc' etc,
 * as of version 0.30.2.
 */
class ExternalAPIsMigration extends \Db\Migration {

  /**
   * Apply only the current migration.
   * @return true on success or false on failure
   */
  function apply(\Db\Connection $db) {
    $mapping = array(
      'blockchain' => 'address_btc',
      'litecoin' => 'address_ltc',
      'feathercoin' => 'address_ftc',
      'ppcoin' => 'address_ppc',
      'novacoin' => 'address_nvc',
      'primecoin' => 'address_xpm',
      'terracoin' => 'address_trc',
      'dogecoin' => 'address_dog',
      'megacoin' => 'address_mec',
      'ripple' => 'address_xrp',
      'namecoin' => 'address_nmc',
      'digitalcoin' => 'address_dgc',
      'worldcoin' => 'address_wdc',
      'ixcoin' => 'address_ixc',
      'vertcoin' => 'address_vtc',
      'netcoin' => 'address_net',
      'hobonickels' => 'address_hbn',
      'blackcoin' => 'address_bc1',
      'darkcoin' => 'address_drk',
      'vericoin' => 'address_vrc',
      'nxt' => 'address_nxt',
      'reddcoin' => 'address_rdd',
      'viacoin' => 'address_via',
      'nubits' => 'address_nbt',
      'nushares' => 'address_nsr',
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
