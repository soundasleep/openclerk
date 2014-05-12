<?php

/**
 * Analog of {@link json_encode()} for .properties keys/values.
 */
function csv_encode($s) {
	if (is_array($s)) {
		$output = "";
		foreach ($s as $value) {
			$output .= ($output ? "," : "") . csv_encode($value);
		}
		return $output . "\n";
	} else {
		return "\"" . str_replace("\"", "\"\"", $s) . "\"";
	}
}

/**
 * Analog of {@link json_decode()} for .properties keys/values.
 */
function csv_decode($s) {
	$s = str_replace("\"\"", "\"", $s);
	if (substr($s, 0, 1) == "\"" && substr($s, -1) == "\"") {
		$s = substr($s, 1, strlen($s) - 2);
	}
	return $s;
}
