<?php

/**
 * Process notification edits, creates and deletes.
 */

require(__DIR__ . "/inc/global.php");
require_login();

$user = get_user(user_id());
require_user($user);

$errors = array();
$messages = array();

// cancel edit
if (require_post("cancel", false)) {
	// redirect back to notifications wizard
	redirect(url_for('wizard_notifications'));
}

// delete - need to do this before create/edit
if (require_post("delete", false)) {
	// get the existing instance
	$q = db()->prepare("SELECT * FROM notifications WHERE id=? AND user_id=?");
	$q->execute(array(require_post("id"), user_id()));
	$instance = $q->fetch();
	if (!$instance) {
		throw new Exception("Could not find any such notification instance to delete");
	}

	// delete all old notification type instances
	switch ($instance['notification_type']) {
		case "ticker":
			$q = db()->prepare("DELETE FROM notifications_ticker WHERE id=?");
			$q->execute(array($instance['type_id']));
			break;

		case "summary_instance":
			$q = db()->prepare("DELETE FROM notifications_summary_instances WHERE id=?");
			$q->execute(array($instance['type_id']));
			break;

		default:
			throw new Exception("Unknown old notification type '" . htmlspecialchars($instance['notification_type']) . "'");
	}

	// delete the instance
	$q = db()->prepare("DELETE FROM notifications WHERE id=? AND user_id=?");
	$q->execute(array(require_post("id"), user_id()));

	$messages[] = "Deleted notification.";
	set_temporary_messages($messages);
	redirect(url_for('wizard_notifications'));

}

// process types into [notification_type] and maybe [exchange]
$type_id = false;
switch (require_post("type")) {
	case "ticker":
		$notification_type = 'ticker';
		break;

	case "summary_instance_total":
		$notification_type = 'summary_instance';
		$summary_type = 'total' . require_post("total_currency");
		break;

	case "summary_instance_total_hashrate":
		$notification_type = 'summary_instance';
		$summary_type = 'totalmh_' . require_post("total_hashrate_currency");
		break;

	case "summary_instance_all2":
		$notification_type = 'summary_instance';
		$summary_type = 'all2' . require_post("all2_summary");
		break;

	default:
		throw new Exception("Unknown type '" . htmlspecialchars(require_post("type")) . "'");
}

// get all of our limits
$accounts = user_limits_summary(user_id());

// editing?
if (require_post("id", false)) {
	// get the existing instance
	$q = db()->prepare("SELECT * FROM notifications WHERE id=? AND user_id=?");
	$q->execute(array(require_post("id"), user_id()));
	$instance = $q->fetch();
	if (!$instance) {
		throw new Exception("Could not find any such notification instance to edit");
	}

	// have we changed types?
	if ($notification_type != $instance['notification_type']) {
		// delete all old notification type instances
		switch ($instance['notification_type']) {
			case "ticker":
				$q = db()->prepare("DELETE FROM notifications_ticker WHERE id=?");
				$q->execute(array($instance['type_id']));

			case "summary_instance":
				$q = db()->prepare("DELETE FROM notifications_summary_instances WHERE id=?");
				$q->execute(array($instance['type_id']));

			default:
				throw new Exception("Unknown old notification type '" . htmlspecialchars($instance['notification_type']) . "'");
		}
		$type_id = false;
	} else {
		// update the existing instance
		$type_id = $instance['type_id'];
	}
} else {
	if (!can_user_add($user, 'notifications')) {
		$errors[] = "Cannot add notification: too many existing notifications.<br>" .
				($user['is_premium'] ? "" : " To add more notifications, upgrade to a <a href=\"" . htmlspecialchars(url_for('premium')) . "\">premium account</a>.");

		set_temporary_errors($errors);
		redirect(url_for('wizard_notifications'));
	}
}

if (require_post('period') == 'hour' && !$user['is_premium']) {
	$errors[] = "Only <a href=\"" . htmlspecialchars(url_for('premium')) . "\">premium accounts</a> can add hourly notifications.";

	set_temporary_errors($errors);
	redirect(url_for('wizard_notifications'));
}

// create or edit a new instance (both edit and create)
switch ($notification_type) {
	case "ticker":
		$currency1 = substr(require_post('currencies'), 0, 3);
		$currency2 = substr(require_post('currencies'), 3, 3);
		if (!in_array($currency1, get_all_currencies())) {
			throw new Exception("'" . htmlspecialchars($currency1) . "' is not a valid currency.");
		}
		if (!in_array($currency2, get_all_currencies())) {
			throw new Exception("'" . htmlspecialchars($currency2) . "' is not a valid currency.");
		}

		if (!$type_id) {
			$q = db()->prepare("INSERT INTO notifications_ticker SET exchange=:exchange, currency1=:currency1, currency2=:currency2");
			$q->execute(array(
				'exchange' => require_post('exchange'),
				'currency1' => $currency1,
				'currency2' => $currency2,
			));
			$type_id = db()->lastInsertId();
		} else {
			$q = db()->prepare("UPDATE notifications_ticker SET exchange=:exchange, currency1=:currency1, currency2=:currency2 WHERE id=:id");
			$q->execute(array(
				'exchange' => require_post('exchange'),
				'currency1' => $currency1,
				'currency2' => $currency2,
				'id' => $type_id,
			));
		}
		break;

	case "summary_instance":
		// TODO we could check that the summary instance type is valid (e.g. totalbtc, crypto2usd, etc) but it would
		// take a lot of infrastructure work, because we don't actually check that all summary instance types ARE valid
		if (strlen($summary_type) > 32 || !$summary_type) {
			throw new Exception("Invalid summary type '" . htmlspecialchars($summary_type) . "'");
		}

		if (!$type_id) {
			$q = db()->prepare("INSERT INTO notifications_summary_instances SET summary_type=:summary_type");
			$q->execute(array(
				'summary_type' => $summary_type,
			));
			$type_id = db()->lastInsertId();
		} else {
			$q = db()->prepare("UPDATE notifications_summary_instances SET summary_type=:summary_type WHERE id=:id");
			$q->execute(array(
				'summary_type' => $summary_type,
				'id' => $type_id,
			));
		}
		break;

	default:
		throw new Exception("Unknown new notification type '" . htmlspecialchars($notification_type) . "'");
}

$permitted_notification_periods = get_permitted_notification_periods();
if (!isset($permitted_notification_periods[require_post("period")])) {
	throw new Exception("Invalid notification period '" . htmlspecialchars(require_post("period")) . "'");
}
// remove any commas
$value = str_replace(",", "", require_post("value"));
if (!is_numeric($value)) {
	throw new Exception("'" . htmlspecialchars($value) . "' is not numeric");
}

$args = array(
	"user_id" => user_id(),
	"type_id" => $type_id,
	"trigger_condition" => require_post("condition"),
	"trigger_value" => $value,
	"is_percent" => require_post("percent", 0) ? 1 : 0,
	"period" => require_post("period"),
	"notification_type" => $notification_type,
);

if (require_post("id", false)) {
	// update existing
	// need to also reset last_value and is_notified so that we don't accidentally send notifications for an old currency
	$q = db()->prepare("UPDATE notifications SET notification_type=:notification_type, trigger_condition=:trigger_condition, trigger_value=:trigger_value, is_percent=:is_percent, period=:period, type_id=:type_id, is_notified=0, last_value=NULL, last_notification=NULL WHERE id=:id AND user_id=:user_id");
	$args += array('id' => $instance['id']);
	$q->execute($args);

	$messages[] = "Updated existing notification.";

} else {
	// create new
	$q = db()->prepare("INSERT INTO notifications SET notification_type=:notification_type, trigger_condition=:trigger_condition, trigger_value=:trigger_value, is_percent=:is_percent, period=:period, type_id=:type_id, is_notified=0, user_id=:user_id");
	$q->execute($args);

	$messages[] = "Created new notification.";
}

// redirect
set_temporary_messages($messages);
set_temporary_errors($errors);

if ($errors) {
	redirect(url_for('wizard_notifications' /* TODO pass along previous values for errors? */));	// go back
} else {
	redirect(url_for('wizard_notifications'));	// go forward
}

