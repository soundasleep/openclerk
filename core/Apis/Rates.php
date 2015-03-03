<?php

namespace Core\Apis;

/**
 * API to get a list of live rates for all supported currencies.
 *
 * Used by Currency Converter Android app.
 */
class Rates extends \Apis\CachedApi {

  function getJSON($arguments) {
    // important for url_for() links
    define('FORCE_NO_RELATIVE', true);

    $result = array();
    $result['currencies'] = array();
    foreach (get_all_currencies() as $cur) {
      $result['currencies'][] = array(
        'code' => $cur,
        'abbr' => get_currency_abbr($cur),
        'name' => get_currency_name($cur),
        'fiat' => is_fiat_currency($cur),
      );
    }

    require(__DIR__ . "/../../inc/api.php");
    $result['rates'] = api_get_all_rates();

    return $result;
  }

  function getEndpoint() {
    return "/api/v1/rates[.json]";
  }

  function getHash($arguments) {
    return "";    // there is nothing to hash
  }

}
