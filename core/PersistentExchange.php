<?php

namespace Core;

use \Openclerk\Currencies\Exchange;
use \Db\Connection;
use \Monolog\Logger;

/**
 * Allows an {@link Exchange} to have data stored persistently,
 * for example storing the supported currencies and the most recent
 * currency rates, in the database - so that they do not have to be
 * refetched every time.
 */
class PersistentExchange {

  function __construct(Exchange $exchange, Connection $db) {
    $this->exchange = $exchange;
    $this->db = $db;
  }

  function storeMarkets($markets, Logger $logger) {
    $logger->info("Storing " . count($markets) . " markets persistently");

    // find all existing markets
    $existing = $this->getMarkets(true);

    // remove removed markets
    foreach ($existing as $pair) {
      if (array_search($pair, $markets) === false) {
        $logger->info("Removing pair " . implode("/", $pair));
        $q = $this->db->prepare("DELETE FROM exchange_pairs WHERE currency1=? AND currency2=?");
        $q->execute(array($pair[0], $pair[1]));
      }
    }

    // add new markets
    foreach ($markets as $pair) {
      if (array_search($pair, $existing) === false) {
        if (strlen($pair[0]) != 3) {
          $logger->info("Ignoring currency '" . $pair[0] . "': not three characters long");
          continue;
        }
        if (strlen($pair[1]) != 3) {
          $logger->info("Ignoring currency '" . $pair[1] . "': not three characters long");
          continue;
        }
        $logger->info("Adding pair " . implode("/", $pair));
        $q = $this->db->prepare("INSERT INTO exchange_pairs SET exchange=?, currency1=?, currency2=?");
        $q->execute(array($this->exchange->getCode(), $pair[0], $pair[1]));
      }
    }

    // reset cache
    $this->cached_markets = null;
  }

  var $cached_markets = null;

  /**
   * Quickly get all markets for this exchange.
   * May be cached.
   * @param force if {@code true}, force a refresh
   */
  function getMarkets($force = false) {
    if ($this->cached_markets === null || $force) {
      $this->cached_markets = array();
      $q = $this->db->prepare("SELECT * FROM exchange_pairs WHERE exchange=?");
      $q->execute(array($this->exchange->getCode()));
      while ($pair = $q->fetch()) {
        $this->cached_markets[] = array($pair['currency1'], $pair['currency2']);
      }
    }
    return $this->cached_markets;
  }

}
