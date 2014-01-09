<?php

/**
 * Summary instance check for notifications.
 * Finds out the changed values for a particular summary instance.
 */

crypto_log("Summary instance type: " . $account['summary_type'] . " for user " . $notification['user_id']);

// get the most recent value
$q = db()->prepare("SELECT * FROM summary_instances WHERE user_id=:user_id AND summary_type=:summary_type AND is_recent=1 LIMIT 1");
$q->execute(array(
	"user_id" => $notification['user_id'],
	"summary_type" => $account['summary_type'],
));
$summary_instance = $q->fetch();
if (!$summary_instance) {
	// TODO maybe support failing for notifications, to disable notifications for e.g. accounts that no longer exist?
	// probably better to make sure that we can never *have* a referenced account that never exists
	throw new JobException("Could not find any recent summary instance values for " . $account['summary_type'] . " for user " . $notification['user_id']);
}

$current_value = $summary_instance['balance'];

// what was the last value?
// may need to generate this if no value exists, but hopefully this only occurs very rarely,
// since this may be a very heavy query
if ($notification['last_value'] === null) {
	crypto_log("No last value found: retrieving");

	// get the query string for this interval
	$periods = get_permitted_notification_periods();
	if (!isset($periods[$notification['period']]['interval'])) {
		throw new JobException("Unknown job period '" . $notification['period'] . "'");
	}
	$period = $periods[$notification['period']]['interval'];

	$q = db()->prepare("SELECT * FROM summary_instances WHERE user_id=:user_id AND summary_type=:summary_type AND created_at <= DATE_SUB(NOW(), $period) ORDER BY id DESC LIMIT 1");
	$q->execute(array(
		"user_id" => $notification['user_id'],
		"summary_type" => $account['summary_type'],
	));
	$last = $q->fetch();
	if (!$last) {
		throw new JobException("Could not find any last values for " . $account['summary_type'] . " for user " . $notification['user_id']);
	}
	$notification['last_value'] = $last['balance'];
}

// other parameters
if (substr($account['summary_type'], 0, strlen('totalmh_')) == 'totalmh_') {
	$currency = substr($account['summary_type'], strlen('totalmh_'));
	$value_label = "MH/s";
} else if (substr($account['summary_type'], 0, strlen('total')) == 'total') {
	$currency = substr($account['summary_type'], strlen('total'));
	$value_label = get_currency_abbr($currency);
} else if (substr($account['summary_type'], 0, strlen('all2')) == 'all2') {
	$summary_type = substr($account['summary_type'], strlen('all2'));
	$summary_types = get_total_conversion_summary_types();
	$value_label = get_currency_abbr($summary_types[$summary_type]['currency']);
} else {
	throw new JobException("Unknown summary_instance summary_type '" . htmlspecialchars($account['summary_type']) . "'");
}
