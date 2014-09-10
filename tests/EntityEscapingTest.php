<?php

require_once(__DIR__ . "/../inc/global.php");
require_once(__DIR__ . "/OpenclerkTest.php");

/**
 * Tests related to the release quality of Openclerk - i.e. more like integration tests.
 */
class EntityEscapingTest extends OpenclerkTest {

	/**
	 * A simple check to make sure that all <a href=""> doesn't use invaild &s in the
	 * target URLs (e.g. "&action=")
	 */
	function testAnyInvalidEntitiesInHrefs() {
		$files = $this->findFiles();
		$this->assertTrue(count($files) > 0);

		foreach ($files as $f) {
			$s = file_get_contents($f);
			if (preg_match_all('#href="([^"]+&[^;"]+)"#i', $s, $matches_array, PREG_SET_ORDER)) {
				foreach ($matches_array as $matches) {
					$this->fail("Found invalid entity in href '{$matches[1]}' in '$f'");
				}
			}
		}
	}

}
