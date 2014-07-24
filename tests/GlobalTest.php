<?php

require_once(__DIR__ . "/../inc/global.php");

/**
 * Tests functionality in global.php.
 */
class GlobalTest extends PHPUnit_Framework_TestCase {

	/**
	 * Tests {@link #is_valid_email()}.
	 * Also checks UTF-8.
	 */
	function testIsValidEmail() {
		$this->assertValidEmail("support@openclerk.org");
		$this->assertValidEmail("support@email.openclerk.org");
		$this->assertValidEmail("support+こんにちは@openclerk.org");
		$this->assertValidEmail("support@こんにちは.com");			// not sure about this one, do mail server support it?
		$this->assertValidEmail("one.two.three@my.travel");
		$this->assertValidEmail("one+two+three@my.travel");
		$this->assertValidEmail("a@b.c");

		$this->assertNotValidEmail("one@two@three.com");
		$this->assertNotValidEmail("abc.example.com");
		$this->assertNotValidEmail("localhost@localdomain");
		$this->assertNotValidEmail("support@ openclerk.org");
		$this->assertNotValidEmail("support @openclerk.org");
		$this->assertNotValidEmail("support @openclerk. org");
		$this->assertNotValidEmail(" support@openclerk.org");
	}

	function assertValidEmail($e) {
		$this->assertTrue(is_valid_email($e), $e . " should be valid");
	}

	function assertNotValidEmail($e) {
		$this->assertFalse(is_valid_email($e), $e . " should not be valid");
	}

	/**
	 * Tests {@link #is_valid_url()}.
	 * Also checks UTF-8.
	 */
	function testIsValidUrl() {
		$this->assertValidUrl("http://openclerk.org");
		$this->assertValidUrl("https://openclerk.org");
		$this->assertValidUrl("http://openclerk.org/?test=1");
		$this->assertValidUrl("http://openclerk.org/?test=1&test=2");
		$this->assertValidUrl("http://openclerk.org/?test=1&amp;test=2");
		$this->assertValidUrl("http://openclerk.org/こんにちは");

		$this->assertNotValidUrl("htp://openclerk.org");
		$this->assertNotValidUrl("http//openclerk.org");
		$this->assertNotValidUrl("https:/openclerk.org");
	}

	function assertValidUrl($e) {
		$this->assertTrue(is_valid_url($e), $e . " should be valid");
	}

	function assertNotValidUrl($e) {
		$this->assertFalse(is_valid_url($e), $e . " should not be valid");
	}

	/**
	 * Basic tests for {@link #url_add()}.
	 */
	function testUrlAdd() {
		$this->assertEquals("url", url_add('url', array()));
		$this->assertEquals("url?key=bar", url_add('url', array('key' => 'bar')));
		$this->assertEquals("url?key=bar&bar=foo", url_add('url', array('key' => 'bar', 'bar' => 'foo')));
	}

	/**
	 * Basic tests for {@link #url_add()} using absolute URLs.
	 */
	function testUrlAddAbsolute() {
		$this->assertEquals("http://openclerk.org/url", url_add('http://openclerk.org/url', array()));
		$this->assertEquals("http://openclerk.org/url?key=bar", url_add('http://openclerk.org/url', array('key' => 'bar')));
		$this->assertEquals("http://openclerk.org/url?key=bar&bar=foo", url_add('http://openclerk.org/url', array('key' => 'bar', 'bar' => 'foo')));
	}

	function testNumberFormatPrecision() {
		$this->assertSame("1", number_format_precision("1.000", 0));
		$this->assertSame("1", number_format_precision("1.100", 0));
		$this->assertSame("0", number_format_precision("0.010", 0));
		$this->assertSame("0", number_format_precision("0.011", 0));
		$this->assertSame("0", number_format_precision("0.0110001", 0));
	}

	function testNumberFormatAutoprecision() {
		$this->assertSame("1", number_format_autoprecision("1.000"));
		$this->assertSame("1.1", number_format_autoprecision("1.100"));
		$this->assertSame("0.01", number_format_autoprecision("0.010"));
		$this->assertSame("0.011", number_format_autoprecision("0.011"));
		$this->assertSame("0.01100010", number_format_autoprecision("0.0110001"));

		$this->assertSame("1", number_format_autoprecision("1.000", 2));
		$this->assertSame("1.10", number_format_autoprecision("1.100", 2));
		$this->assertSame("0.01", number_format_autoprecision("0.010", 2));
		$this->assertSame("0.01", number_format_autoprecision("0.011", 2));
		$this->assertSame("0.01", number_format_autoprecision("0.0110001", 2));
	}

	function testNumberFormatHuman() {
		$this->assertSame("1", number_format_human("1.000"));
		$this->assertSame("1.1", number_format_human("1.100"));
		$this->assertSame("0.01", number_format_human("0.010"));
		$this->assertSame("0.0110", number_format_human("0.011"));
		$this->assertSame("0.0110", number_format_human("0.0110001"));
		$this->assertSame("0.00001", number_format_human("0.00001"));
		$this->assertSame("0.000001", number_format_human("0.000001"));
		$this->assertSame("0.00000010", number_format_human("0.0000001"));
	}

}
