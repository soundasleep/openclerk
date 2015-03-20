<?php

require_once(__DIR__ . "/../inc/global.php");
require_once(__DIR__ . "/OpenclerkTest.php");

/**
 * Issue #271: The `version` field in `package.json` should be updated automatically at build.
 */
class GenerateVersionTest extends OpenclerkTest {

  function getPackageJsonVersion() {
    $version = get_site_config('openclerk_version');
    if (!preg_match("#^[0-9]+\.[0-9]+\.[0-9]+$#", $version)) {
      $version = $version . ".0";
    }
    return $version;
  }

  function testGenerate() {
    $version = $this->getPackageJsonVersion();
    $found = false;

    $this->assertNotEmpty($version, "No version found");
    $this->assertMatches("#^[0-9]+\.[0-9]+\.[0-9]+$#", $version, "Version was a valid package.json version");

    $input = file(__DIR__ . "/../package.json");
    $fp = fopen(__DIR__ . "/../package.json", "w");
    foreach ($input as $line) {
      if (preg_match('#^(.*?"version": ")[^"]+?(",.*?)$#i', $line, $matches)) {
        $line = $matches[1] . $version . $matches[2] . "\n";
        $found = true;
      }
      fwrite($fp, $line);
    }
    fclose($fp);

    $this->assertTrue($found, "Could not find 'version' field in package.json");

  }

}
