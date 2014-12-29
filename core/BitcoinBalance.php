<?php

namespace Core;

use \Openclerk\Config;
use \Apis\Fetch;
use \Openclerk\Currencies\BalanceException;
use \Monolog\Logger;

/**
 * Represents something that can fetch Bitcoin balances.
 *
 * Blockchain job (BTC).
 */
class BitcoinBalance {

  var $address;
  var $is_received;

  function __construct($address, $is_received = false) {
    $this->address = $address;
    $this->is_received = $is_received;
  }

  /**
   *
   * @throws {@link BalanceException} if something happened and the balance could not be obtained.
   */
  function getBalance(Logger $logger) {
    if ($this->is_received) {
      $logger->info("Need to get received balance rather than current balance");
      $url = "https://blockchain.info/q/getreceivedbyaddress/" . urlencode($this->address) . "?confirmations=" . get_site_config('btc_confirmations');
    } else {
      $url = "https://blockchain.info/q/addressbalance/" . urlencode($this->address) . "?confirmations=" . get_site_config('btc_confirmations');
    }

    if (Config::get('blockchain_api_key')) {
      $logger->info("Using Blockchain API key.");
      $url = url_add($url, array('api_code' => Config::get('blockchain_api_key')));
    }

    $logger->info($url);
    $balance = Fetch::get($url);
    $divisor = 1e8;   // divide by 1e8 to get btc balance

    if (!is_numeric($balance)) {
      $logger->error("Blockchain balance for " . htmlspecialchars($this->address) . " is non-numeric: " . htmlspecialchars($balance));
      if ($balance == "Checksum does not validate") {
        throw new BalanceException("Checksum does not validate");
      }
      if (strpos($balance, "Maximum concurrent requests reached.") !== false) {
        throw new BlockchainException("Maximum concurrent requests reached");
      }
      throw new BalanceException("Blockchain returned non-numeric balance: '" . htmlspecialchars($balance) . "'");
    } else {
      $logger->info("Blockchain balance for " . htmlspecialchars($this->address) . ": " . ($balance / $divisor));
    }

    return $balance / $divisor;
  }

}
