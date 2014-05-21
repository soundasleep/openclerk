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
		'fr',
		'lolcat',
	);
}

function get_locale_label($locale) {
	switch ($locale) {
		case "en": return "English";
		case "fr": return "French";
		case "lolcat": return "Lolcat";

		default:
			throw new LocaleException("Unknown locale for label '$locale'");
	}
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

	// remove any unnecessary whitespace in the key that won't be displayed
	$key = trim(preg_replace("/[\\s]{2,}/im", " ", $key));

	global $global_loaded_locales;
	if ($locale != 'en' && !isset($global_loaded_locales[$locale])) {
		$result = false;
		if (!file_exists(__DIR__ . "/../locale/" . $locale . ".php")) {
			if (!in_array($locale, get_all_locales()) && $locale != 'en') {
				// reset back to 'en'
				set_locale('en');
				return t_without_category($key, $args);
			}

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
		if (substr($k, 0, 1) !== ":") {
			throw new LocaleException("Did not expect non-parameterised key '$k'");
		}
	}

	// add default arguments
	if (!isset($args[':site_name'])) {
		$args[':site_name'] = get_site_config('site_name');
	}

	if (!isset($global_loaded_locales[$locale][$key])) {
		if ($locale != 'en' && function_exists('missing_locale_string') && get_site_config("log_missing_i18n")) {
			missing_locale_string($key, $locale);
		}
		if (is_admin() && get_site_config('show_i18n')) {
			return "[" . strtr($key, $args) . "]";
		} else {
			return strtr($key, $args);
		}
	}
	if (is_admin() && get_site_config('show_i18n')) {
		return "[" . strtr($global_loaded_locales[$locale][$key], $args) . "]";
	} else {
		return strtr($global_loaded_locales[$locale][$key], $args);
	}
}

/**
 * Helper function for {@code htmlspecialchars(t(...))}.
 * @see t()
 */
function ht($category, $key = false, $args = array()) {
	return htmlspecialchars(t($category, $key, $args));
}

/**
 * Return the plural of something.
 * e.g. plural('book', 1), plural('book', 'books', 1), plural('book', 1000)
 */
function plural($string, $strings, $number = false, $decimals = 0) {
	// old format
	if (is_numeric($string)) {
		if ($number === false) {
			return plural($strings, $strings . "s", $string, $decimals);
		} else {
			return plural($strings, $number, $string, $decimals);
		}
	}

	// no second parameter provided
	if ($number === false) {
		return plural($string, $string . "s", $strings, $decimals);
	}

	if ($number == 1) {
		return sprintf("%s %s", number_format($number, $decimals), t($string));
	} else {
		return sprintf("%s %s", number_format($number, $decimals), t($strings));
	}
}
