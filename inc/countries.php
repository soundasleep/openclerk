<?php

/**
 * ISO 3166-1 country list: two-character codes with each ISO-recognised countries, dependent territories and states.
 * Obtained with https://github.com/umpirsky/country-list (issue #424).
 * Should be idential to http://en.wikipedia.org/wiki/ISO_3166-1.
 * Should be updated regularly as necessary.
 */
function get_country_iso() {
  return require(__DIR__ . "/../vendor/openclerk/country-list/country/en/country.php");
}
