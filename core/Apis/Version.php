<?php

namespace Core\Apis;

/**
 * Get current Openclerk version.
 */
class Version extends \Apis\CachedApi {

  function getJSON($arguments) {
    return get_site_config('openclerk_version');
  }

  function getEndpoint() {
    return "/api/v1/version[.json]";
  }

  function getHash($arguments) {
    return ""; // nothing to hash
  }

}
