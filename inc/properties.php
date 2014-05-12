<?php

/**
 * Simple functions for accessing Java .properties files.
 * See http://docs.oracle.com/javase/7/docs/api/java/util/Properties.html#load%28java.io.Reader%29
 */

/**
 * Analog of {@link json_encode()} for .properties keys/values.
 */
function properties_encode($s) {
	$s = str_replace("\\", "\\\\", $s);
	$s = str_replace(":", "\\:", $s);
	$s = str_replace("=", "\\=", $s);
	// TODO what about strings starting with # or !?
	return $s;
}

/**
 * Analog of {@link json_decode()} for .properties keys/values.
 */
function properties_decode($s) {
	$s = str_replace("\\=", "=", $s);
	$s = str_replace("\\:", ":", $s);
	$s = str_replace("\\\\", "\\", $s);
	// TODO what about strings starting with # or !?
	return $s;
}
