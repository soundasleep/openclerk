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

}
