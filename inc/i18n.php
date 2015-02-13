<?php

/*
 * A simple class to support internationalisation (i18n)
 * through the t() method.
 * The locale needs to be loaded at runtime either through
 * session data, cookies, databases etc.
 * Also see the methods for getting the list of available locales.
 */

use \Openclerk\I18n;
use \Openclerk\Locale;

class LocaleException extends Exception { }

class GenericLocale implements Locale {

  var $key;
  var $title;

  function __construct($key, $title) {
    $this->key = $key;
    $this->title = $title;
  }

  function getKey() {
    return $this->key;
  }

  function getTitle() {
    return $this->title;
  }

  function load() {
    require(__DIR__ . "/../locale/" . $this->key . ".php");
    return $result;
  }

}

$locales = array(
  'de' => 'German' /* i18n */,
  // 'en' is automatic
  'fr' => 'French' /* i18n */,
  'jp' => 'Japanese' /* i18n */,
  'ru' => 'Russian' /* i18n */,
  'zh' => 'Chinese' /* i18n */,
);
foreach ($locales as $locale => $title) {
  I18n::addAvailableLocale(new GenericLocale($locale, $title));
}

I18n::addDefaultKeys(array(
  ':site_name' => get_site_config('site_name'),
));

// set locale as necessary
if (isset($_COOKIE["locale"]) && in_array($_COOKIE["locale"], array_keys(I18n::getAvailableLocales()))) {
  I18n::setLocale($_COOKIE["locale"]);
}
