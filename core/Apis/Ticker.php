<?php

namespace Core\Apis;

/**
 * Get all supported ticker markets and details for the given exchange.
 *
 * @param exchange the unique exchange code
 */
class Ticker extends \Apis\CachedApi {

  function getJSON($arguments) {
    $instance = \DiscoveredComponents\Exchanges::getInstance($arguments['exchange']);
    $key = $instance->getCode();

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

    return array(
      "code" => $instance->getCode(),
      "name" => $instance->getName(),
      "disabled" => $instance instanceof \Openclerk\Currencies\DisabledExchange,
      "rates" => $rates,
    );
  }

  function getEndpoint() {
    return "/api/v1/ticker/:exchange[.json]";
  }

  function getHash($arguments) {
    return "";    // there is nothing to hash
  }

}
