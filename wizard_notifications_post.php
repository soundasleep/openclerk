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

	default:
		throw new Exception("Unknown type '" . htmlspecialchars(require_post("type")) . "'");
}

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

			default:
				throw new Exception("Unknown old notification type '" . htmlspecialchars($instance['notification_type']) . "'");
		}
		$type_id = false;
	} else {
		// update the existing instance
		$type_id = $instance['type_id'];
	}
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

	default:
		throw new Exception("Unknown new notification type '" . htmlspecialchars($notification_type) . "'");
}

$permitted_notification_periods = get_permitted_notification_periods();
if (!isset($permitted_notification_periods[require_post("period")])) {
	throw new Exception("Invalid notification period '" . htmlspecialchars(require_post("period")) . "'");
}
if (!is_numeric(require_post("value"))) {
	throw new Exception("'" . htmlspecialchars(require_post("value")) . "' is not numeric");
}

$args = array(
	"user_id" => user_id(),
	"type_id" => $type_id,
	"trigger_condition" => require_post("condition"),
	"trigger_value" => require_post("value"),
	"is_percent" => require_post("percent", 0) ? 1 : 0,
	"period" => require_post("period"),
	"notification_type" => $notification_type,
);

if (require_post("id", false)) {
	// update existing
	$q = db()->prepare("UPDATE notifications SET notification_type=:notification_type, trigger_condition=:trigger_condition, trigger_value=:trigger_value, is_percent=:is_percent, period=:period, type_id=:type_id, is_notified=0 WHERE id=:id AND user_id=:user_id");
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

