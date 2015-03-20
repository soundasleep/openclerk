<?php

/**
 * Allows us to load client-specific locale strings.
 */

define('FORCE_NO_RELATIVE', true);    // url_for() references need to be relative to the base path, not the js/ directory that this script is within

require(__DIR__ . "/../../inc/content_type/js.php");    // to allow for appropriate headers etc
require(__DIR__ . "/../../inc/global.php");

use \Openclerk\I18n;
use \Openclerk\Locale;

// note that the contents of this file will change based on user, selected currencies etc;
// these parameters need to be encoded into a ?hash parameter, so that while this file can
// be cached, it is correctly reloaded when necessary.
allow_cache();

$locale = require_get("locale");
if (!in_array($locale, array_keys(I18n::getAvailableLocales()))) {
  throw new Exception("Locale '$locale' is not a valid locale");
}
I18n::setLocale($locale);

$strings = json_decode(file_get_contents(__DIR__ . "/../../locale/client.json"));
$result = array();
foreach ($strings as $key) {
  $result[$key] = t($key);
}

?>
window.LocaleStrings = <?php echo json_encode($result); ?>;
