<?php

/**
 * Defines all of the interesting properties of the web appliation:
 * what currencies are supported, what pairs, etc.
 */

function get_all_currencies() {
	return array("btc", "ltc", "nmc", "usd", "nzd");
}

function is_valid_btc_address($address) {
	// very simple check according to https://bitcoin.it/wiki/Address
	if (strlen($address) >= 27 && strlen($address) <= 34 && (substr($address, 0, 1) == "1" || substr($address, 0, 1) == "3")
			&& preg_match("#^[A-Za-z0-9]+$#", $address)) {
		return true;
	}
	return false;
}