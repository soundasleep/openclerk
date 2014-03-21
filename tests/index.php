<?php

// to access this page, you need to be an administrator
require(__DIR__ . "/../inc/global.php");
require_admin();

require_once(__DIR__ . "/../inc/simpletest/autorun.php");

set_time_limit(180);	// make it long, but not too long

class AllTests extends TestSuite {

	function __construct() {
		parent::__construct();

		$only = require_get("only", false);

		// we just load all PHP files within this directory
		if ($handle = opendir('.')) {
			echo "<ul style=\"padding: 10px; list-style: none;\">";
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != ".." && substr(strtolower($entry), -4) == ".php" && strtolower($entry) != 'index.php') {
					if ($only && $entry !== $only) {
						continue;
					}
					$this->addFile($entry);

					echo "<li style=\"display: inline-block; margin-right: 5px;\"><a href=\"" . url_for('tests/', array('only' => $entry)) . "\">" . htmlspecialchars($entry) . "</a></li>\n";
				}
			}
			echo "</ul>";
			closedir($handle);
		}
	}

}

