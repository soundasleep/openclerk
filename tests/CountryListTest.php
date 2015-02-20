<?php

require_once(__DIR__ . "/../inc/global.php");

class CountryListTest extends PHPUnit_Framework_TestCase {

  function testCountryListExists() {
    $this->assertGreaterThan(0, count(get_country_iso()));
  }

  function testCountryListContainsCommonCountries() {
    $keys = array_keys(get_country_iso());

    foreach (array('US', 'GB', 'NZ', 'AU', 'ZA') as $iso) {
      $this->assertTrue(in_array($iso, $keys), "'$iso' was not in country list");
    }
  }

  function testCountryListDoesNotHaveUnknown() {
    $keys = array_keys(get_country_iso());

    foreach (array('ZZ') as $iso) {
      $this->assertFalse(in_array($iso, $keys), "'$iso' should not be in country list");
    }
  }

}
