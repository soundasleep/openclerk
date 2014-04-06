<?php

/**
 * A naive implementation of simple string caching.
 */

class CacheException extends Exception { }

/**
 * Naive caching. Get the most recent cache value or recompile it using a callback.
 * Uses key/hash storage.
 *
 * @param $hash must be <32 chars
 * @param $age the maximum age for the cache in seconds
 * @param $callback the function which will generate the content if the cache is invalidated or missing
 */
function compile_cached($key, $hash, $age, $callback) {
	if (strlen($hash) > 255) {
		throw new CacheException("Cannot cache with a key longer than 255 characters");
	}
	if (strlen($hash) > 32) {
		throw new CacheException("Cannot cache with a hash longer than 32 characters");
	}

	$q = db()->prepare("SELECT * FROM cached_strings WHERE cache_key=? AND cache_hash=? AND created_at >= DATE_SUB(NOW(), INTERVAL $age SECOND)");
	$q->execute(array($key, $hash));
	if ($cache = $q->fetch()) {
		$result = $cache['content'];
	} else {
		$result = $callback();
		$q = db()->prepare("DELETE FROM cached_strings WHERE cache_key=? AND cache_hash=?");
		$q->execute(array($key, $hash));

		if (strlen($result) >= pow(2, 24)) {
			throw new CacheException("Cache value is too large (> 16 MB)");
		}

		$q = db()->prepare("INSERT INTO cached_strings SET cache_key=?, cache_hash=?, content=?");
		$q->execute(array($key, $hash, $result));
	}

	return $result;
}
