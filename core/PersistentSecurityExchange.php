<?php

namespace Core;

use \Openclerk\Currencies\SecurityExchange;
use \Db\Connection;
use \Monolog\Logger;

/**
 * Allows a {@link SecurityExchange} to have data stored persistently,
 * for example storing the supported securities - so that they do not have to be
 * refetched every time.
 */
class PersistentSecurityExchange {

  function __construct(SecurityExchange $exchange, Connection $db) {
    $this->exchange = $exchange;
    $this->db = $db;
  }

  function storeSupportedSecurities($securities, Logger $logger) {
    $logger->info("Storing " . count($securities) . " securities persistently");

    // find all existing securities
    $existing = $this->getSupportedSecurities(true);

    // remove removed securities
    foreach ($existing as $security) {
      if (array_search($security, $securities) === false) {
        $key = $security['currency'] . "/" . $security['security'];
        $logger->info("Removing security $key");
        $q = $this->db->prepare("DELETE FROM security_exchange_securities WHERE exchange=? AND currency=? AND security=?");
        $q->execute(array($this->exchange->getCode(), $security['currency'], $security['security']));
      }
    }

    // add new securities
    foreach ($securities as $security) {
      if (array_search($security, $existing) === false) {
        $key = $security['currency'] . "/" . $security['security'];

        if (strlen($security['currency']) != 3) {
          $logger->info("Ignoring security '" . $security . "': currency not three characters long");
          continue;
        }
        $logger->info("Adding security $key");
        $q = $this->db->prepare("INSERT INTO security_exchange_securities SET exchange=?, currency=?, security=?");
        $q->execute(array($this->exchange->getCode(), $security['currency'], $security['security']));
      }
    }

    // reset cache
    $this->cached_securities = null;
  }

  var $cached_securities = null;

  /**
   * Quickly get all supported securities for this security exchange.
   * May be cached.
   * @param force if {@code true}, force a refresh
   */
  function getSupportedSecurities($force = false) {
    if ($this->cached_securities === null || $force) {
      $this->cached_securities = array();
      $q = $this->db->prepare("SELECT * FROM security_exchange_securities WHERE exchange=?");
      $q->execute(array($this->exchange->getCode()));
      while ($c = $q->fetch()) {
        $this->cached_securities[] = array(
          'currency' => $c['currency'],
          'security' => $c['security'],
        );
      }
    }
    return $this->cached_securities;
  }

}
