<?php

namespace Core\Apis;

/**
 * API to get details about a specific currency.
 *
 * @param currency three-character unique currency code
 */
class Currency extends \Apis\CachedApi {

  function getJSON($arguments) {
    $instance = \DiscoveredComponents\Currencies::getInstance($arguments['currency']);
    return array(
      "code" => $instance->getCode(),
      "abbr" => $instance->getAbbr(),
      "name" => $instance->getName(),
      "cryptocurrency" => $instance->isCryptocurrency(),
      "fiat" => $instance->isFiat(),
      "commodity" => $instance->isCommodity(),
    );
  }

  function getEndpoint() {
    return "/api/v1/currency/:currency[.json]";
  }

  function getHash($arguments) {
    return $arguments['currency'];
  }

}
