<?php

namespace Core\Apis;

/**
 * Get the latest rates for the given currency pair
 * across all tracked exchanges.
 *
 * @param currency1 first currency, e.g. 'nzd'
 * @param currency2 second currency, e.g. 'btc'
 */
class Rate extends \Apis\CachedApi {

  function getJSON($arguments) {
    // important for url_for() links
    define('FORCE_NO_RELATIVE', true);

    if (!in_array($arguments['currency1'], get_all_currencies())) {
      throw new Exception("Invalid currency '" . $arguments['currency1'] . "'");
    }

    if (!in_array($arguments['currency2'], get_all_currencies())) {
      throw new Exception("Invalid currency '" . $arguments['currency2'] . "'");
    }

    $q = db()->prepare("SELECT * FROM ticker_recent WHERE currency1=? AND currency2=? ORDER BY volume DESC");
    $q->execute(array($arguments['currency1'], $arguments['currency2']));

    $result = array();
    while ($ticker = $q->fetch()) {
      $result[] = array(
        'exchange' => $ticker['exchange'],
        'last_trade' => $ticker['last_trade'],
        'bid' => $ticker['bid'],
        'ask' => $ticker['ask'],
        "volume" => $ticker['volume'],
        'time' => $ticker['created_at'],
        'url' => absolute_url(url_for('historical', array('id' => $ticker['exchange'] . "_" . $ticker['currency1'] . $ticker['currency2'] . "_daily"))),
      );
    }

    if (!$result) {
      throw new Exception("No rates found");
    }

    return $result;
  }

  function getEndpoint() {
    return "/api/v1/rate/:currency1/:currency2[.json]";
  }

  function getHash($arguments) {
    return $arguments['currency1'] . $arguments['currency2'];    // there is nothing to hash
  }

}
