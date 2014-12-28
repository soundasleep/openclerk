<?php

namespace Core;

/**
 * Represents the Bitcoin cryptocurrency.
 */
class Bitcoin extends \Openclerk\Currencies\Cryptocurrency {

  function getCode() {
    return "btc";
  }

  function getName() {
    return "Bitcoin";
  }

  function getURL() {
    return "http://bitcoin.org/";
  }

  function getCommunityLinks() {
    return array(
      "https://www.weusecoins.com/en/" => "What is Bitcoin?",
    );
  }

  function isValid($address) {
    // TODO move into this class
    return is_valid_btc_address($address);
  }

  function hasExplorer() {
    return true;
  }

  function getExplorerName() {
    return "Blockchain";
  }

  function getExplorerURL() {
    return "https://blockchain.info/";
  }

  function getBalanceURL($address) {
    return sprintf("https://blockchain.info/address/%s", urlencode($address));
  }

  function getBalance($address) {
    throw new \Exception("Not implemented");
  }

}
