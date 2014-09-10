<?php

require_once(__DIR__ . "/../inc/global.php");

/**
 * Tests related to the release quality of Openclerk - i.e. more like integration tests.
 */
class OpenclerkTest extends PHPUnit_Framework_TestCase {

	function findFiles() {
		return $this->recurseFindFiles(array(".", "inc/classes"), "");
	}

	function recurseFindFiles($dirs, $name) {
		if (!is_array($dirs)) {
			$dirs = array($dirs);
		}

		$result = array();
		foreach ($dirs as $dir) {
			if ($handle = opendir($dir)) {
				while (false !== ($entry = readdir($handle))) {
					if ($entry != "." && $entry != "..") {
						if (substr(strtolower($entry), -4) == ".php") {
							$result[] = $dir . "/" . $entry;
						} else if (is_dir($dir . "/" . $entry)) {
							if ($name == 'inc') {
								// ignore subdirs of inc
								continue;
							}
							if ($name == 'vendor') {
								// ignore subdirs of vendor
								continue;
							}
							if ($name == 'git') {
								// ignore 'git' dir (temporarily)
								continue;
							}
							$result = array_merge($result, $this->recurseFindFiles($dir . "/" . $entry, $entry));
						}
					}
				}
				closedir($handle);
			}
		}
		return $result;
	}

	function assertMatches($regexp, $string, $message = false) {
		if (!preg_match($regexp, $string)) {
			if ($message === false) {
				$this->fail("'$string' did not match '$regexp'");
			} else {
				$this->fail("$message: '$string' did not match '$regexp'");
			}
		}
	}

}
