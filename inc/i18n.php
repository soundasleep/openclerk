<?php

/*
 * A simple class to support internationalisation (i18n)
 * through the t() method.
 * The locale needs to be loaded at runtime either through
 * session data, cookies, databases etc.
 * Also see the methods for getting the list of available locales.
 */

class LocaleException extends Exception { }

/**
 * Set the current session locale.
 * Assumes the session has started.
 * @throws LocaleException if the specified locale is not a vaild locale, maybe catch and reset to 'en'
 */
function set_locale($locale) {
	if (!in_array($locale, get_all_locales())) {
		throw new LocaleException("Locale '$locale' does not exist");
	}
	$_SESSION['locale'] = $locale;
}

/**
 * Get all available locales as a list of locale keys.
 */
function get_all_locales() {
	return array(
		'en',
		'meow',
	);
}

/**
 * Get the current locale, or 'en' if none is defined.
 */
function get_current_locale() {
	$locale = require_session('locale', false);
	return $locale ? $locale : 'en';
}

$global_loaded_locales = array();

/**
 * Translate a given 'en' string into the current locale. Loads locale data
 * from {@code __DIR__ . "/../locale/" . $locale . ".php"}.
 *
 * If the given string does not exist in the locale then
 * call {@code missing_locale_string($key, $locale)} (if the function exists)
 * and return the default translation (in 'en').
 *
 * @see set_locale($locale)
 * @see missing_locale_string($key, $locale)
 */
function t($category, $key = false, $args = array()) {
	if (is_string($category) && is_string($key)) {
		return t_without_category($key, $args);
	} else {
		if ($key === false) {
			return t_without_category($category);
		} else {
			return t_without_category($category, $key);
		}
	}
}

function t_without_category($key = false, $args = array()) {
	$locale = get_current_locale();

	global $global_loaded_locales;
	if (!isset($global_loaded_locales[$locale])) {
		$result = false;
		if (!file_exists(__DIR__ . "/../locale/" . $locale . ".php")) {
			throw new LocaleException("Could not load locale '$locale' data");
		}
		require(__DIR__ . "/../locale/" . $locale . ".php");
		if (!$result) {
			throw new LocaleException("Locale '$locale' did not load any data");
		}
		$global_loaded_locales[$locale] = $result;
	}

	if (!is_array($args)) {
		throw new LocaleException("Expected array argument");
	}
	foreach ($args as $k => $value) {
		if (is_numeric($k)) {
			throw new LocaleException("Did not expect numeric key '$k'");
		}
	}

	if (!isset($global_loaded_locales[$locale][$key])) {
		if (function_exists('missing_locale_string')) {
			missing_locale_string($key, $locale);
		}
		return strtr($key, $args);
	}
	return strtr($global_loaded_locales[$locale][$key], $args);
}

