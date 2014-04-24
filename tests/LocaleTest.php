<?php

require_once(__DIR__ . "/../inc/global.php");

/**
 * Tests locale functionality.
 */
class LocaleTest extends PHPUnit_Framework_TestCase {

	/**
	 * Tests {@link t()} functionality.
	 * We're testing the search/replace functionality rather than locale loading at this point.
	 */
	function testTStrtr() {
		$this->assertEquals("Hello meow 1", t("Hello :world 1", array(':world' => 'meow')));
		$this->assertEquals(":hello :hi 2", t(":hi :hello 2", array(':hi' => ':hello', ':hello' => ':hi')));
		$this->assertEquals("Hello :world 3", t("Hello :world 3", array(':meow' => ':world')));

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

		// these should all throw exceptions
		try {
			$this->assertEquals("Hello meow", t("test", "Hello :world", array('test')));
			$this->fail("Expected LocaleException");
		} catch (LocaleException $e) {
			// expected
		}
	}

}
