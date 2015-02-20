<?php

require_once(__DIR__ . "/../inc/global.php");
require_once(__DIR__ . "/OpenclerkTest.php");

use \Openclerk\I18n;
use \Openclerk\LocaleException;

/**
 * Tests locale functionality.
 */
class LocaleTest extends OpenclerkTest {

  /**
   * Tests {@link t()} functionality.
   * We're testing the search/replace functionality rather than locale loading at this point.
   */
  function testTStrtr() {
    $this->assertEquals("Hello meow 1", t("Hello :world 1", array(':world' => 'meow')));
    $this->assertEquals(":hello :hi 2", t(":hi :hello 2", array(':hi' => ':hello', ':hello' => ':hi')));
    $this->assertEquals("Hello :world 3", t("Hello :world 3", array(':meow' => ':world')));
    $this->assertEquals("Hello :world 4", t("Hello :world 4"));
    $this->assertEquals("Hello :world 5", t("Hello   :world \r\n 5"));

    // these should all throw exceptions
    try {
      $this->assertEquals("Hello meow", t("Hello :world", array('test')));
      $this->fail("Expected LocaleException");
    } catch (LocaleException $e) {
      // expected
    }
  }

  /**
   * Tests {@link t()} functionality, that the developer can also specify
   * a category as part of the function.
   */
  function testTCategory() {
    $this->assertEquals("Hello meow 1", t("test", "Hello :world 1", array(':world' => 'meow')));
    $this->assertEquals(":hello :hi 2", t("test", ":hi :hello 2", array(':hi' => ':hello', ':hello' => ':hi')));
    $this->assertEquals("Hello :world 3", t("test", "Hello :world 3", array(':meow' => ':world')));
    $this->assertEquals("Hello :world 4", t("test", "Hello :world 4"));
    $this->assertEquals("Hello :world 5", t("test", "Hello   :world \r\n 5"));

    // these should all throw exceptions
    try {
      $this->assertEquals("Hello meow", t("test", "Hello :world", array('test')));
      $this->fail("Expected LocaleException");
    } catch (LocaleException $e) {
      // expected
    }
  }

  /**
   * Tests that all locales defined by {@link get_all_locales()} exist.
   */
  function testAllLocales() {
    foreach (I18n::getAvailableLocales() as $locale => $instance) {
      if ($locale == 'en') {
        continue;
      }

      $f = __DIR__ . "/../locale/" . $locale . ".php";
      $this->assertTrue(file_exists($f), "Locale file " . $f . " should exist");
    }
  }

  /**
   * Tests the {@link plural()} function.
   */
  function testPlural() {
    $this->assertEquals("1 account", plural("account", 1));
    $this->assertEquals("2 accounts", plural("account", 2));
    $this->assertEquals("1 account", plural("account", "accounts", 1));
    $this->assertEquals("9 accounts", plural("account", "accounts", 9));
    $this->assertEquals("1,000 accounts", plural("account", "accounts", 1000));
    $this->assertEquals("9 addresses", plural("account", "addresses", 9));
  }

  /**
   * Tests the {@link plural()} function in the old calling style.
   */
  function testPluralOld() {
    $this->assertEquals("1 account", plural(1, "account"));
    $this->assertEquals("2 accounts", plural(2, "account"));
    $this->assertEquals("1 account", plural(1, "account", "accounts"));
    $this->assertEquals("9 accounts", plural(9, "account", "accounts"));
    $this->assertEquals("1,000 accounts", plural(1000, "account", "accounts"));
    $this->assertEquals("9 addresses", plural(9, "account", "addresses"));
  }

}
