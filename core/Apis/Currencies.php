<?php

namespace Core\Apis;

/**
 * API to get a list of all supported currencies.
 */
class Currencies extends \Apis\CachedApi {

  function getJSON($arguments) {
    $result = array();
    foreach (get_all_currencies() as $cur) {
      $instance = \DiscoveredComponents\Currencies::getInstance($cur);
      $result[] = array(
        "code" => $instance->getCode(),
        "abbr" => $instance->getAbbr(),
        "name" => $instance->getName(),
        "cryptocurrency" => $instance->isCryptocurrency(),
        "fiat" => $instance->isFiat(),
        "commodity" => $instance->isCommodity(),
      );
    }

    return $result;
  }

  function getEndpoint() {
    return "/api/v1/currencies[.json]";
  }

  function getHash($arguments) {
    return "";    // there is nothing to hash
  }

}
