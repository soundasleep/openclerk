<?php

require_once(__DIR__ . "/../inc/global.php");

require_once(__DIR__ . "/../inc/properties.php");

/**
 * Tests for {@link properties_encode()} and {@link properties_decode()}.
 */
class PropertiesEncodeTest extends PHPUnit_Framework_TestCase {

	function testPropertiesEncode() {

		$this->assertEquals("hello", properties_encode("hello"));
		$this->assertEquals("hello\\:", properties_encode("hello:"));
		$this->assertEquals("hello\\=", properties_encode("hello="));
		$this->assertEquals("hello\\\\world", properties_encode("hello\\world"));

	}

	function testPropertiesDecode() {

		$this->assertEquals("hello", properties_decode("hello"));
		$this->assertEquals("hello:", properties_decode("hello\\:"));
		$this->assertEquals("hello=", properties_decode("hello\\="));
		$this->assertEquals("hello\\world", properties_decode("hello\\\\world"));

	}

}
