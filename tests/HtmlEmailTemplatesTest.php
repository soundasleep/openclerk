<?php

require_once(__DIR__ . "/../inc/global.php");

/**
 * Make sure that all email templates can be processed by html2text.
 */
class HtmlEmailTemplatesTest extends PHPUnit_Framework_TestCase {

  function testAll() {
    $dir = __DIR__ . "/../emails/";
    $found = false;

    $this->assertNotFalse($handle = opendir($dir));
    while (false !== ($entry = readdir($handle))) {
      if ($entry != "." && $entry != "..") {
        if (substr(strtolower($entry), -5) == ".html") {
          $file = file_get_contents($dir . $entry);

          $previous = libxml_use_internal_errors(true);
          $this->assertNotNull(Html2Text\Html2Text::convert($file), "Could not convert '$entry'");

          foreach (libxml_get_errors() as $error) {
            $this->fail("Could not load '$entry': " . $error->message);
          }

          libxml_clear_errors();
          libxml_use_internal_errors($previous);

          $found = true;

        }
      }
    }
    closedir($handle);

    $this->assertNotFalse($found, "Did not find any email templates to test in '$dir'");
  }

}
