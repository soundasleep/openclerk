<?php

namespace Core\Apis;

/**
 * List all discovered APIs.
 */
class ApiList extends \Apis\ApiList\ApiListApi {

  function getAPIs() {
    return \DiscoveredComponents\Apis::getAllInstances();
  }

  function getEndpoint() {
    return "/api/v1/apis[.json]";
  }

  function getHash($arguments) {
    return "";    // there is nothing to hash
  }

}
