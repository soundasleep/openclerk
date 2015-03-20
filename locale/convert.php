<?php

/**
 * Batch script to convert `translated/*_locale.json` into local `locale.php` includes.
 */

require(__DIR__ . "/../inc/global.php");

$languages = explode(",", isset($argv[1]) ? $argv[1] : "");

$dir = __DIR__ . "/translated/";
if ($dh = opendir($dir)) {
  while (($file = readdir($dh)) !== false) {
    $matches = false;
    if (preg_match("/_([a-z@]+).json$/i", $file, $matches) && !preg_match("/^locale_/i", $file)) {
      $locale = $matches[1];

      // switch over specific locales
      switch ($locale) {
        case "en@lolcat":
          $locale = "lolcat";
          continue;
      }

      if ($languages && !in_array($locale, $languages)) {
        echo "skipping locale $locale\n";
        continue;
      }

      echo $dir . $file . " -> " . $locale . "\n";

      $json = json_decode(file_get_contents($dir . $file));
      if (!$json) {
        throw new Exception("Could not load $dir$file: invalid JSON");
      }

      // now write locale.php
      $fp = fopen(__DIR__ . "/" . $locale . ".php", "w");
      fwrite($fp, "<?php\n\n/**\n * $locale template file\n * Generated from '$file' at " . date('r') . "\n */\n\n");
      fwrite($fp, '$' . "result = array(\n");
      foreach ($json as $key => $value) {
        fwrite($fp, "\t\"" . phpescapestring($key) . "\" => \"" . phpescapestring($value) . "\",\n");
      }
      fwrite($fp, ");\n");
      fclose($fp);

    }
  }
  closedir($dh);
}
