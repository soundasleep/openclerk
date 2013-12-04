<?php

// to access this page, you need to be an administrator
require(__DIR__ . "/../inc/global.php");
require_admin();

require_once(__DIR__ . "/../inc/simpletest/autorun.php");

set_time_limit(180);	// make it long, but not too long

class AllTests extends TestSuite {

	function __construct() {
		parent::__construct();
		// we just load all PHP files within this directory
		if ($handle = opendir('.')) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != ".." && substr(strtolower($entry), -4) == ".php" && strtolower($entry) != 'index.php') {
					$this->addFile($entry);
				}
			}
			closedir($handle);
		}
	}

}