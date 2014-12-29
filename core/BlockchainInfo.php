<?php

namespace Core;

use \Openclerk\Config;
use \Apis\Fetch;
use \Openclerk\Currencies\BalanceException;
use \Openclerk\Currencies\BlockException;
use \Openclerk\Currencies\DifficultyException;
use \Monolog\Logger;

/**
 * Represents something that can fetch Bitcoin statistics.
 *
 * Blockchain job (BTC).
 */
class BlockchainInfo {

  /**
   *
   * @throws {@link BalanceException} if something happened and the balance could not be obtained.
   */
  function getBalance($address, Logger $logger, $is_received = false) {
    if ($is_received) {
      $logger->info("Need to get received balance rather than current balance");
      $url = "https://blockchain.info/q/getreceivedbyaddress/" . urlencode($address) . "?confirmations=" . get_site_config('btc_confirmations');
    } else {
      $url = "https://blockchain.info/q/addressbalance/" . urlencode($address) . "?confirmations=" . get_site_config('btc_confirmations');
    }

    if (Config::get('blockchain_api_key', false)) {
      $logger->info("Using Blockchain API key.");
      $url = url_add($url, array('api_code' => Config::get('blockchain_api_key')));
    }

    $logger->info($url);
    $balance = Fetch::get($url);
    $divisor = 1e8;   // divide by 1e8 to get btc balance

    if (!is_numeric($balance)) {
      $logger->error("Blockchain balance for " . htmlspecialchars($address) . " is non-numeric: " . htmlspecialchars($balance));
      if ($balance == "Checksum does not validate") {
        throw new BalanceException("Checksum does not validate");
      }
      if (strpos($balance, "Maximum concurrent requests reached.") !== false) {
        throw new BlockchainException("Maximum concurrent requests reached");
      }
      throw new BalanceException("Blockchain returned non-numeric balance: '" . htmlspecialchars($balance) . "'");
    } else {
      $logger->info("Blockchain balance for " . htmlspecialchars($address) . ": " . ($balance / $divisor));
    }

    return $balance / $divisor;
  }

  /**
   *
   * @throws {@link BlockException} if something happened and the balance could not be obtained.
   */
  function getBlockCount(Logger $logger) {
    $url = "https://blockchain.info/q/getblockcount";

    if (Config::get('blockchain_api_key', false)) {
      $logger->info("Using Blockchain API key.");
      $url = url_add($url, array('api_code' => Config::get('blockchain_api_key')));
    }

    $logger->info($url);
    $value = Fetch::get($url);

    if (!is_numeric($value)) {
      $logger->error("Block count is non-numeric: " . htmlspecialchars($value));
      throw new BlockException("Blockchain returned non-numeric value: '" . htmlspecialchars($value) . "'");
    } else {
      $logger->info("Block count : " . $value);
    }

    return $value;
  }

  /**
   *
   * @throws {@link DifficultyException} if something happened and the balance could not be obtained.
   */
  function getDifficulty(Logger $logger) {
    $url = "https://blockchain.info/q/getdifficulty";

    if (Config::get('blockchain_api_key', false)) {
      $logger->info("Using Blockchain API key.");
      $url = url_add($url, array('api_code' => Config::get('blockchain_api_key')));
    }

    $logger->info($url);
    $value = Fetch::get($url);

    if (!is_numeric($value)) {
      $logger->error("Difficulty is non-numeric: " . htmlspecialchars($value));
      throw new BlockException("Blockchain returned non-numeric value: '" . htmlspecialchars($value) . "'");
    } else {
      $logger->info("Difficulty : " . $value);
    }

    return $value;
  }

}
