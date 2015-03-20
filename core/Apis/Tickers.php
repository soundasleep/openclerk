<?php

namespace Core\Apis;

/**
 * API to get a list of all supported tickers.
 */
class Tickers extends \Apis\CachedApi {

  function getJSON($arguments) {
    $result = array();
    foreach (\DiscoveredComponents\Exchanges::getAllInstances() as $key => $instance) {
      $q = db()->prepare("SELECT * FROM ticker_recent WHERE exchange=?");
      $q->execute(array($key));
      $rates = array();
      while ($rate = $q->fetch()) {
        $rates[] = array(
          'currency1' => $rate['currency1'],
          'currency2' => $rate['currency2'],
          'last_trade' => $rate['last_trade'],
          'updated' => $rate['created_at'],
        );
      }

      $result[] = array(
        "code" => $instance->getCode(),
        "name" => $instance->getName(),
        "disabled" => $instance instanceof \Openclerk\Currencies\DisabledExchange,
        "rates" => $rates,
      );
    }

    return $result;
  }

  function getEndpoint() {
    return "/api/v1/tickers[.json]";
  }

  function getHash($arguments) {
    return "";    // there is nothing to hash
  }

}
