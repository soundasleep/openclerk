<?php

/**
 * Openclerk version check job.
 */

$exchange = "version_check";

crypto_log("Local version: " . get_site_config('openclerk_version'));

// call cryptfolio.com to find the latest openclerk.org version
$version = crypto_get_contents(crypto_wrap_url(url_add('https://cryptfolio.com/version', array(
		// pass some parameters to help capture number of running public instances
		'absolute_url' => get_site_config('absolute_url'),
		'openclerk_version' => get_site_config('openclerk_version'),
	))));
crypto_log("Remote version: " . $version);

if (!$version) {
	throw new ExternalAPIException("Could not retrieve remote Openclerk version");
}

// compare
if (version_compare($version, get_site_config('openclerk_version')) > 0) {
	// the remote version is newer
	$q = db()->prepare("SELECT * FROM admin_messages WHERE message_type=? AND is_read=0 LIMIT 1");
	$q->execute(array('version_check'));
	if (!$q->fetch()) {
		$q = db()->prepare("INSERT INTO admin_messages SET message_type=?, message=?");
		$q->execute(array('version_check', '<a href="http://openclerk.org">A new version</a> of Openclerk is available: ' . $version));
		crypto_log("Inserted new admin_message.");
	}
}

