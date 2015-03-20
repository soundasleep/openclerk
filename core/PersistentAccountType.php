<?php

namespace Core;

use \Account\AccountType;
use \Db\Connection;
use \Monolog\Logger;

/**
 * Allows an {@link AccountType} to have data stored persistently,
 * for example storing the supported currencies - so that they do not have to be
 * refetched every time.
 */
class PersistentAccountType {

  function __construct(AccountType $exchange, Connection $db) {
    $this->exchange = $exchange;
    $this->db = $db;
  }

  function storeSupportedCurrencies($currencies, Logger $logger) {
    $logger->info("Storing " . count($currencies) . " currencies persistently");

    // find all existing currencies
    $existing = $this->getSupportedCurrencies(true);

    // remove removed currencies
    foreach ($existing as $currency) {
      if (array_search($currency, $currencies) === false) {
        $logger->info("Removing currency $currency");
        $q = $this->db->prepare("DELETE FROM account_currencies WHERE exchange=? AND currency=?");
        $q->execute(array($this->exchange->getCode(), $currency));
      }
    }

    // add new currencies
    foreach ($currencies as $currency) {
      if (array_search($currency, $existing) === false) {
        if (strlen($currency) != 3) {
          $logger->info("Ignoring currency '" . $currency . "': not three characters long");
          continue;
        }
        $logger->info("Adding currency $currency");
        $q = $this->db->prepare("INSERT INTO account_currencies SET exchange=?, currency=?");
        $q->execute(array($this->exchange->getCode(), $currency));
      }
    }

    // reset cache
    $this->cached_currencies = null;
  }

  var $cached_currencies = null;

  /**
   * Quickly get all supported currencies for this account.
   * May be cached.
   * @param force if {@code true}, force a refresh
   */
  function getSupportedCurrencies($force = false) {
    if ($this->cached_currencies === null || $force) {
      $this->cached_currencies = array();
      $q = $this->db->prepare("SELECT * FROM account_currencies WHERE exchange=?");
      $q->execute(array($this->exchange->getCode()));
      while ($c = $q->fetch()) {
        $this->cached_currencies[] = $c['currency'];
      }
    }
    return $this->cached_currencies;
  }

}
