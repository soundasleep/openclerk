<?php

namespace Core\Apis;

/**
 * List all discovered APIs, ordered by endpoint.
 */
class ApiList extends \Apis\ApiList\ApiListApi {

  function getAPIs() {
    $apis = \DiscoveredComponents\Apis::getAllInstances();
    usort($apis, array($this, 'sortByEndpoint'));
    return $apis;
  }

  function sortByEndpoint($a, $b) {
    return strcmp($a->getEndpoint(), $b->getEndpoint());
  }

  function getEndpoint() {
    return "/api/v1/apis[.json]";
  }

  function getHash($arguments) {
    return "";    // there is nothing to hash
  }

}
