<?php

/**
 * Notification job.
 * Checks to see if a notification should be sent out; if so, sends the notification out.
 *
 * Also see notification/<type>.php, which gets the most recent values, does any calculations, and
 * decides whether a notification should be sent out.
 */

// get the relevant notification
$user = get_user($job['user_id']);
$q = db()->prepare("SELECT * FROM notifications WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$notification = $q->fetch();
if (!$notification) {
	throw new JobException("Cannot find a notification " . $job['arg_id'] . " for user " . $job['user_id']);
}

// get the account details for the notification
switch ($notification['notification_type']) {
	case "ticker":
		$q = db()->prepare("SELECT * FROM notifications_ticker WHERE id=?");
		$q->execute(array($notification['type_id']));
		$account = $q->fetch();
		if (!$account) {
			throw new JobException("Cannot find a notification type '" . htmlspecialchars($notification['notification_type']) . "' " . $notification['type_id']);
		}

		// do calculations
		require(__DIR__ . "/notifications/ticker.php");
		break;

	case "summary_instance":
		$q = db()->prepare("SELECT * FROM notifications_summary_instances WHERE id=?");
		$q->execute(array($notification['type_id']));
		$account = $q->fetch();
		if (!$account) {
			throw new JobException("Cannot find a notification type '" . htmlspecialchars($notification['notification_type']) . "' " . $notification['type_id']);
		}

		// do calculations
		require(__DIR__ . "/notifications/summary_instance.php");
		break;

	default:
		throw new JobException("Unknown notification type '" . htmlspecialchars($notification['notification_type']) . "'");
}

crypto_log("Current value: " . $current_value);
crypto_log("Last value: " . $notification['last_value']);
crypto_log("Trigger condition: " . $notification['trigger_condition'] . " " . $notification['trigger_value'] . ($notification['is_percent'] ? "%" : ""));

// we now have $notification['last_value'] set, $notification['last_checked'] set, and $current_value
// or $current_value does not exist (exception thrown) or $notification['last_value'] does not exist (exception thrown)
$value_delta = ($current_value - $notification['last_value']);
if ($notification['last_value'] == 0) {
	$percent = null;
} else {
	$percent = ($current_value - $notification['last_value']) / $notification['last_value'];
}
switch ($notification['trigger_condition']) {
	case "increases":
		// true if the value increases at all
		$should_notify = ($current_value > $notification['last_value']);
		break;

	case "increases_by":
		// true if the value increases by a given amount
		if ($notification['is_percent']) {
			$delta = $percent;
		} else {
			$delta = $value_delta;
		}
		$should_notify = ($delta != null) && ($delta >= $notification['trigger_value']);
		break;

	case "above":
		// true if the value is above a given amount, AND we haven't already notified the user
		$should_notify = ($current_value > $notification['trigger_value']) && !$notification['is_notified'];
		break;

	case "decreases":
		// true if the value decreases at all
		$should_notify = ($current_value < $notification['last_value']);
		break;

	case "decreases_by":
		// true if the value decreases by a given amount
		if ($notification['is_percent']) {
			$delta = $percent;
		} else {
			$delta = $value_delta;
		}
		$should_notify = ($delta != null) && (-$delta >= $notification['trigger_value']);
		break;

	case "below":
		// true if the value is below a given amount, AND we haven't already notified the user
		$should_notify = ($current_value < $notification['trigger_value']) && !$notification['is_notified'];
		break;

	default:
		throw new JobException("Unknown trigger condition '" . $notification['trigger_condition'] . "'");
}

if ($should_notify) {
	crypto_log("Trigger successful.");

	// calculate text for this notification
	switch ($notification['trigger_condition']) {
		case "increases":
			$change_text = "increased";
			break;

		case "increases_by":
			$change_text = "increased by " . number_format_autoprecision($notification['trigger_value'], 4) . ($notification['is_percent'] ? '%' : (" " . $value_label));
			break;

		case "above":
			$change_text = "is above " . number_format_autoprecision($notification['trigger_value'], 4) . " " . $value_label;
			break;

		case "decreases":
			$change_text = "decreased";
			break;

		case "decreased_by":
			$change_text = "decreased by " . number_format_autoprecision($notification['trigger_value'], 4) . ($notification['is_percent'] ? '%' : (" " . $value_label));
			break;

		case "below":
			$change_text = "decreased below " . number_format_autoprecision($notification['trigger_value'], 4) . " " . $value_label;
			break;

		default:
			throw new JobException("Unknown trigger condition for change text: '" . $notification['trigger_condition'] . "'");
	}

	// send the email
	if ($user['email']) {
		$args = array(
			"name" => ($user['name'] ? $user['name'] : $user['email']),
			"url" => absolute_url(url_for('wizard_notifications')),
			"last_value" => number_format_autoprecision($notification['last_value'], 3),
			"current_value" => number_format_autoprecision($current_value, 3),
			"value_label" => $value_label,
			"value_delta" => number_format_autoprecision($value_delta, 3),
			"percent" => $percent === null ? "infinite%" : number_format_autoprecision($percent * 100, 3),
			"change_text" => $change_text,
			"period" => $notification['period'],
		);
		switch ($notification['notification_type']) {
			case "ticker":
				$email_template = 'notification_ticker';
				$args += array(
					"exchange" => get_exchange_name($account['exchange']),
					"currency1" => get_currency_abbr($account['currency1']),
					"currency2" => get_currency_abbr($account['currency2']),
				);
				break;

			case "summary_instance":
				$email_template = 'notification_summary_instance';
				if (substr($account['summary_type'], 0, strlen('total')) == 'total') {
					$currency = substr($account['summary_type'], strlen('total'));
					$args += array(
						"label" => "total " . get_currency_abbr($currency),
					);
				} else if (substr($account['summary_type'], 0, strlen('all2')) == 'all2') {
					$summary_type = substr($account['summary_type'], strlen('all2'));
					$summary_types = get_total_conversion_summary_types();
					$args += array(
						"label" => "converted " . $summary_types[$summary_type]['short_title'],
					);
				} else {
					throw new JobException("Unknown summary_instance summary_type '" . htmlspecialchars($account['summary_type']) . "'");
				}
				$args['label_uc'] = capitalize($args['label']);
				break;

			default:
				throw new JobException("Unknown notification type for email '" . $notification['notification_type'] . "'");
		}

		send_email($user['email'], ($user['name'] ? $user['name'] : $user['email']), $email_template, $args);
		crypto_log("Sent notification e-mail to " . htmlspecialchars($user['email']) . ".");
	}

	// update the notification
	$q = db()->prepare("UPDATE notifications SET is_notified=1,last_notification=NOW(),last_value=? WHERE id=?");
	$q->execute(array($current_value, $notification['id']));

} else {
	crypto_log("Trigger not successful.");

	// update the notification
	$q = db()->prepare("UPDATE notifications SET is_notified=0,last_value=? WHERE id=?");
	$q->execute(array($current_value, $notification['id']));
}
