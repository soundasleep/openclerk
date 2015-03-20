<?php

/**
 * Using http://translate.google.com/toolkit/ we can automate the process of translating
 * common strings. There are some changes that need to be done:
 *
 * - Placeholders of ":key" format are translated. Using "<key>" is better, but sometimes
 *   placeholders are still lost; we need to check that the translated file still holds
 *   ALL of the same placeholders.
 *
 * - We assume that the translated file `translated/*_locale.txt` has the exact same format,
 *   order and number of lines as the local `template.txt`. Otherwise there is nothing
 *   that we can do.
 *
 * - Google Translate allows one to upload new versions of the same translation template,
 *   and previously completed translations will automatically be matched as part of the TM.
 */

require(__DIR__ . "/../inc/global.php");

$input = explode("\n", trim(str_replace("\r", "", file_get_contents(__DIR__ . "/template.txt"))));

$dir = __DIR__ . "/translated/";
if ($dh = opendir($dir)) {
  while (($file = readdir($dh)) !== false) {
    $matches = false;
    if (preg_match("/_([a-z@]+).txt$/i", $file, $matches)) {
      $locale = $matches[1];

      echo $dir . $file . " -> " . $locale . "\n";

      $f = file_get_contents($dir . $file);
      $is_utf8 = false;
      // strip out UTF-8 header...
      if (substr($f, 0, strlen("\xEF\xBB\xBF")) == "\xEF\xBB\xBF") {
        echo "[processing utf-8]\n";
        $f = substr($f, strlen("\xEF\xBB\xBF"));
        $is_utf8 = true;
      }
      $translated = explode("\n", trim(str_replace("\r", "", $f)));

      if (count($input) != count($translated)) {
        echo $input[0] . "\n";
        echo $input[1] . "\n";
        echo $translated[0] . "\n";
        echo $translated[1] . "\n";
        throw new Exception("Could not translate $dir$file: expected " . count($input) . " lines, found " . count($translated) . " lines");
      }

      // check all placeholders are there
      for ($i = 0; $i < count($input); $i++) {
        if (preg_match_all("/<([a-z_]+)>/i", $input[$i], $placeholders, PREG_SET_ORDER)) {
          foreach ($placeholders as $placeholder) {
            if (strpos($translated[$i], "<" . $placeholder[1] . ">") === false) {
              throw new Exception("Line " . ($i+1) . ": Expected <" . $placeholder[1] . "> in '" . trim($translated[$i]) . "' for '" . $input[$i] . "'");
            }
          }
        }
      }

      // now we can write locale.php
      $fp = fopen(__DIR__ . "/" . $locale . ".php", "w");
      if ($is_utf8) {
        // put back UTF-8 header
        fwrite($fp, "\xEF\xBB\xBF");
      }
      fwrite($fp, "<?php\n\n/**\n * $locale template file\n * Generated from '$file' at " . date('r') . "\n */\n\n");
      fwrite($fp, '$' . "result = array(\n");
      for ($i = 0; $i < count($input); $i++) {
        $input_replaced = preg_replace("/<([a-z0-9_]+)>/i", ":\\1", $input[$i]);
        $translation_replaced = preg_replace("/<([a-z0-9_]+)>/i", ":\\1", $translated[$i]);
        $translation_replaced = trim($translation_replaced);
        fwrite($fp, "\t\"" . phpescapestring($input_replaced) . "\" => \"" . phpescapestring($translation_replaced) . "\",\n");
      }
      fwrite($fp, ");\n");
      fclose($fp);

      // also write a locale_locale.json for loading into Transifex
      $fp = fopen(__DIR__ . "/translated/locale_" . $locale . ".json", "w");
      // no UTF-8 header; json_encode will deal with UTF-8
      fwrite($fp, "{");
      for ($i = 0; $i < count($input); $i++) {
        $input_replaced = preg_replace("/<([a-z0-9_]+)>/i", ":\\1", $input[$i]);
        $translation_replaced = preg_replace("/<([a-z0-9_]+)>/i", ":\\1", $translated[$i]);
        $translation_replaced = trim($translation_replaced);
        fwrite($fp, ($i == 0 ? "" : ",") . "\n\t" . json_encode($input_replaced) . ": " . json_encode($translation_replaced));
      }
      fwrite($fp, "\n}");
      fclose($fp);

    }
  }
  closedir($dh);
}
