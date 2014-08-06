<?php

require_once(__DIR__ . "/../inc/global.php");
require_once(__DIR__ . "/OpenclerkTest.php");

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
		foreach (get_all_locales() as $locale) {
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

	/**
	 * Iterate through the site and find as many i18n strings as we can.
	 * This assumes the whole site uses good code conventions: {@code $i . t("foo")} rather than {@code $i.t("foo")} etc.
	 */
	function testGeneratei18nStrings() {
		$files = $this->recurseFindFiles(".", "");
		$this->assertTrue(count($files) > 0);

		$found = array();

		foreach ($files as $f) {
			// don't look within tests folders
			if (strpos(str_replace("\\", "/", $f), "/tests/") !== false) {
				continue;
			}
			$input = file_get_contents($f);

			// find instances of t() and ht()
			$matches = false;
			if (preg_match_all("#[ \t\n(][hc]?t\\((|['\"][^\"]+[\"'],[ \t\n])\"([^\"]+)\"(|,[ \t\n].+?)\\)#ims", $input, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					// remove whitespace that will never display
					$match[2] = strip_i18n_key($match[2]);
					$found[$match[2]] = $match[2];
				}
			}
			if (preg_match_all("#[ \t\n(][hc]?t\\((|['\"][^\"]+[\"'],[ \t\n])'([^']+)'(|,[ \t\n].+?)\\)#ims", $input, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					// remove whitespace that will never display
					$match[2] = strip_i18n_key($match[2]);
					$found[$match[2]] = $match[2];
				}
			}

			// find instances of plural()
			if (preg_match_all("#[ \t\n(]plural\\(\"([^\"]+)\",[ \t\n][^\"].+?\\)#ims", $input, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					// remove whitespace that will never display
					$match[1] = strip_i18n_key($match[1]);
					$found[$match[1]] = $match[1];
					$found[$match[1] . "s"] = $match[1] . "s";
				}
			}
			if (preg_match_all("#[ \t\n(]plural\\(\"([^\"]+)\",[ \t\n]\"([^\"]+)\",[ \t\n][^\"].+?\\)#ims", $input, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					// remove whitespace that will never display
					$match[1] = strip_i18n_key($match[1]);
					$match[2] = strip_i18n_key($match[2]);
					$found[$match[1]] = $match[1];
					$found[$match[2]] = $match[2];
				}
			}
		}

		$this->assertTrue(count($found) > 0);
		sort($found);

		// we can't have any keys that use HTML like <i>: conversion to/from google will mess them up into :i placeholders
		foreach ($found as $value) {
			if (preg_match("#</?[a-z]+>#im", $value)) {
				throw new Exception("i18n key '" . $value . "' uses HTML, which is not allowed");
			}
		}

		// write them out to a common file
		$fp = fopen(__DIR__ . "/../locale/template.json", 'w');
		fwrite($fp, "{");
		// fwrite($fp, "  \"__comment\": " . json_encode("Generated language template file - do not modify directly"));
		$first = true;
		foreach ($found as $key) {
			if (!$first) {
				fwrite($fp, ",");
			}
			$first = false;
			fwrite($fp, "\n  " . json_encode($key) . ": " . json_encode($key));
		}
		fwrite($fp, "\n}\n");
		fclose($fp);

		// write them out to a common file
		$fp = fopen(__DIR__ . "/../locale/template.txt", 'w');
		foreach ($found as $key) {
			// we need to replace :placeholder with <placeholder>
			$key = preg_replace("/:([a-z0-9_]+)/i", "<\\1>", $key);
			fwrite($fp, $key . "\n");
		}
		fclose($fp);

	}

	/**
	 * Iterate through the site and find as many i18n strings as we can, that are used on the client side (with 'ct()').
	 * Saves the result to {@code locale/client.json} as a list of keys.
	 * This assumes the whole site uses good code conventions: {@code $i . t("foo")} rather than {@code $i.t("foo")} etc.
	 */
	function testGeneratei18nStringsForClient() {
		$files = $this->recurseFindFiles(".", "");
		$this->assertTrue(count($files) > 0);

		$found = array();

		foreach ($files as $f) {
			// don't look within tests folders
			if (strpos(str_replace("\\", "/", $f), "/tests/") !== false) {
				continue;
			}
			$input = file_get_contents($f);

			// find instances of t() and ht()
			$matches = false;
			if (preg_match_all("#[ \t\n(]ct\\((|['\"][^\"]+[\"'],[ \t\n])\"([^\"]+)\"(|,[ \t\n].+?)\\)#ims", $input, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					// remove whitespace that will never display
					$match[2] = strip_i18n_key($match[2]);
					$found[$match[2]] = $match[2];
				}
			}
			if (preg_match_all("#[ \t\n(]ct\\((|['\"][^\"]+[\"'],[ \t\n])'([^']+)'(|,[ \t\n].+?)\\)#ims", $input, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					// remove whitespace that will never display
					$match[2] = strip_i18n_key($match[2]);
					$found[$match[2]] = $match[2];
				}
			}
		}

		$this->assertTrue(count($found) > 0);
		sort($found);

		// write them out to a common file
		$fp = fopen(__DIR__ . "/../locale/client.json", 'w');
		fwrite($fp, json_encode(array_values($found), JSON_PRETTY_PRINT));
		fclose($fp);
	}

}
