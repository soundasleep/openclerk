<?php

class BlockedException extends Exception { }

/**
 * We're about to perform a computationally intense task that is visible
 * or accessible to the public - this method will check the current user
 * IP and make sure this IP isn't requesting too many things at once.
 *
 * If login does not work, make sure that you have set database_timezone
 * correctly.
 */
function check_heavy_request() {

	if (get_site_config("heavy_requests_seconds") >= 0) {

		$q = db()->prepare("SELECT * FROM heavy_requests WHERE user_ip=?");
		$q->execute(array(user_ip()));
		if ($heavy = $q->fetch()) {
			// too many requests?
			// assumes the database and server times are in sync
			if (strtotime($heavy['last_request']) > strtotime("-" . get_site_config("heavy_requests_seconds") . " seconds")) {
				throw new BlockedException("You are making too many requests at once: please wait at least " . number_format(get_site_config("heavy_requests_seconds")) . " seconds.");
			} else {
				// update database
				$q = db()->prepare("UPDATE heavy_requests SET last_request=NOW() WHERE user_ip=?");
				$q->execute(array(user_ip()));
			}

		} else {
			// insert into database
			$q = db()->prepare("INSERT INTO heavy_requests SET last_request=NOW(), user_ip=?");
			$q->execute(array(user_ip()));

		}

	}

}