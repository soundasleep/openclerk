<?php

/**
 * This page is the fourth page in a series of wizards to configure e-mail notifications.
 * A user may revisit this page at any time to reconfigure their notifications.
 */

require(__DIR__ . "/inc/global.php");
require_login();

require(__DIR__ . "/layout/templates.php");
page_header("Notification Preferences", "page_wizard_notifications", array('jquery' => true, 'js' => array('common', 'wizard', 'notifications', 'accounts' /* for sorting */), 'class' => 'page_accounts'));

$user = get_user(user_id());
require_user($user);

$messages = array();

// get all of our accounts
$accounts = user_limits_summary(user_id());

// get all of our notifications
$q = db()->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY notification_type DESC, id ASC");
$q->execute(array(user_id()));
$notifications = $q->fetchAll();

// are we editing one?
$instance = false;
$account = false;
if (require_get("edit", false)) {
	$q = db()->prepare("SELECT * FROM notifications WHERE id=? AND user_id=?");
	$q->execute(array(require_get("edit"), user_id()));
	$instance = $q->fetch();
	if (!$instance) {
		$errors[] = "Could not find your notification " . htmlspecialchars(require_get("edit")) . " to edit.";
	}

	switch ($instance['notification_type']) {
		case "ticker":
			$q = db()->prepare("SELECT * FROM notifications_ticker WHERE id=?");
			$q->execute(array($instance['type_id']));
			$account = $q->fetch();
			// if false, we can still display default editing options
			break;

		case "summary_instance":
			$q = db()->prepare("SELECT * FROM notifications_summary_instances WHERE id=?");
			$q->execute(array($instance['type_id']));
			$account = $q->fetch();
			// if false, we can still display default editing options
			break;

		default:
			throw new Exception("Unknown notification type to edit '" . $instance['notification_type'] . "'");
	}
}

require_template("wizard_notifications");

?>

<div class="wizard">

<?php
/**
 * We use Javascript to update the list of notifications, so that it is easy to edit them and we don't
 * have to write the same interface twice.
 */
?>
<script type="text/javascript">
function get_supported_notifications() {
	return <?php
		// get a list of all possible notifications
		$supported_notifications = array(
			'exchanges' => array(),
			'total_currencies' => array(),
			'total_hashrate_currencies' => array(),
			'all2_summaries' => array(),
		);
		$supported_exchange_currencies = array();

		// all the exchanges we may be interested in
		require(__DIR__ . "/graphs/util.php");
		$summaries = get_all_summary_currencies();
		$conversions = get_all_conversion_currencies();

		foreach (get_exchange_pairs() as $exchange => $pairs) {
			foreach ($pairs as $pair) {
				if (isset($summaries[$pair[0]]) && isset($summaries[$pair[1]])) {
					if (!isset($supported_notifications['exchanges'][$exchange])) {
						$supported_notifications['exchanges'][$exchange] = array();
					}
					$supported_notifications['exchanges'][$exchange][] = $pair;
					$supported_exchange_currencies[$pair[0] . $pair[1]] = get_currency_abbr($pair[0]) . "/" . get_currency_abbr($pair[1]);
				}
			}
		}

		foreach (get_summary_types() as $key => $summary) {
			$cur = $summary['currency'];
			if (isset($summaries[$summary['currency']])) {
				$supported_notifications['total_currencies'][$cur] = get_currency_abbr($cur);
				if (in_array($summary['currency'], get_all_hashrate_currencies())) {
					$supported_notifications['total_hashrate_currencies'][$cur] = get_currency_abbr($cur);
				}
			}
		}

		foreach (get_total_conversion_summary_types() as $key => $summary) {
			if (isset($conversions['summary_' . $key])) {
				$supported_notifications['all2_summaries'][$key] = $summary['short_title'];
			}
		}

		echo json_encode($supported_notifications);
		?>;
}
</script>

<?php if ($instance) { ?>
<h2>Edit Notification</h2>
<?php } else { ?>
<h2>Add New Notification</h2>
<?php } ?>

<form action="<?php echo htmlspecialchars(url_for('wizard_notifications_post')); ?>" method="post">
<table class="notification_template<?php echo $instance ? " selected" : ""; ?>">
<tr>
	<td>
	<span class="email_notification">Please send me an e-mail when</span>
	<select id="notification_type" name="type">
		<option value="ticker"<?php echo ($instance && $instance['notification_type'] == 'ticker') ? " selected" : ""; ?>>the exchange rate</option>
		<option value="summary_instance_total"<?php echo ($instance && $instance['notification_type'] == 'summary_instance' && $account && substr($account['summary_type'], 0, strlen('total')) == 'total' && substr($account['summary_type'], 0, strlen('totalmh_')) != 'totalmh_') ? " selected" : ""; ?>>my total</option>
		<option value="summary_instance_total_hashrate"<?php echo ($instance && $instance['notification_type'] == 'summary_instance' && $account && substr($account['summary_type'], 0, strlen('totalmh_')) == 'totalmh_') ? " selected" : ""; ?>>my total hashrate</option>
		<option value="summary_instance_all2"<?php echo ($instance && $instance['notification_type'] == 'summary_instance' && $account && substr($account['summary_type'], 0, strlen('all2')) == 'all2') ? " selected" : ""; ?>>my converted</option>
	</select>

	<ul>
		<li class="exchanges">
			on
			<select id="notification_exchanges" name="exchange">
				<?php foreach ($supported_notifications['exchanges'] as $exchange => $pairs) { ?>
					<option value="<?php echo htmlspecialchars($exchange); ?>"<?php echo isset($account['exchange']) && $account['exchange'] == $exchange ? " selected" : ""; ?>><?php echo htmlspecialchars(get_exchange_name($exchange)); ?></option>
				<?php } ?>
			</select>

			for
			<select id="notification_currencies" name="currencies">
				<?php foreach ($supported_exchange_currencies as $key => $value) {
					$selected = isset($account['currency1']) && isset($account['currency2']) && ($account['currency1'] . $account['currency2']) == $key; ?>
					<option value="<?php echo htmlspecialchars($key); ?>"<?php echo $selected ? " selected" : ""; ?>><?php echo htmlspecialchars($value); ?></option>
				<?php } ?>
			</select>
		</li>

		<li class="total_currencies" style="display:none;">
			<select id="notification_total_currencies" name="total_currency">
				<?php foreach ($supported_notifications['total_currencies'] as $cur => $title) {
					$selected = $account && $account['summary_type'] == 'total' . $cur; ?>
					<option value="<?php echo htmlspecialchars($cur); ?>"<?php echo $selected ? " selected" : ""; ?>><?php echo htmlspecialchars($title); ?></option>
				<?php } ?>
			</select>
			(before any conversions)
		</li>

		<li class="total_hashrate_currencies" style="display:none;">
			for
			<select id="notification_total_hashrate_currencies" name="total_hashrate_currency">
				<?php foreach ($supported_notifications['total_hashrate_currencies'] as $cur => $title) {
					$selected = $account && $account['summary_type'] == 'totalmh_' . $cur; ?>
					<option value="<?php echo htmlspecialchars($cur); ?>"<?php echo $selected ? " selected" : ""; ?>><?php echo htmlspecialchars($title); ?></option>
				<?php } ?>
			</select>
		</li>

		<li class="all2_summaries" style="display:none;">
			<select id="notification_all2_summaries" name="all2_summary">
				<?php foreach ($supported_notifications['all2_summaries'] as $key => $title) {
					$selected = $account && $account['summary_type'] == 'all2' . $key; ?>
					<option value="<?php echo htmlspecialchars($key); ?>"<?php echo $selected ? " selected" : ""; ?>><?php echo htmlspecialchars($title); ?></option>
				<?php } ?>
			</select>
		</li>

		<li class="condition">
			<select id="notification_condition" name="condition">
				<?php
				$options = get_permitted_notification_conditions();
				foreach ($options as $key => $value) { ?>
				<option value="<?php echo htmlspecialchars($key); ?>"<?php echo $instance && $instance['trigger_condition'] == $key ? " selected" : ""; ?>><?php echo htmlspecialchars($value); ?></option>
				<?php } ?>
			</select>

			<span class="notification_value">
				<input type="text" name="value" value="<?php echo number_format_autoprecision($instance ? $instance['trigger_value'] : 1); ?>">

				<span class="notification_percent_on">
				<select id="notification_percent" name="percent">
					<option value="1"<?php echo $instance ? ($instance['is_percent'] ? " selected" : "") : " selected"; ?>>%</option>
					<option value="0" class="value_label"<?php echo $instance ? ($instance['is_percent'] ? "" : " selected") : ""; ?>>USD/BTC</option>
				</select>
				</span>
				<span class="notification_percent_off value_label">USD/BTC</span>
			</span>
		</li>

		<li class="period">
			within
			<select id="notification_period" name="period">
				<?php
				foreach (get_permitted_notification_periods() as $key => $value) { ?>
				<option value="<?php echo htmlspecialchars($key); ?>"<?php echo $instance && $instance['period'] == $key ? " selected" : ""; ?>><?php echo htmlspecialchars($value['label']); ?></option>
				<?php } ?>
			</select>
		</li>
	</ul>

	</td>
</tr>
<tr>
	<td class="buttons">
		<?php if ($instance) { ?>
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($instance['id']); ?>">
		<input type="submit" name="save" value="Save Notification" class="save">
		<input type="submit" name="cancel" value="Cancel Edit" class="cancel">
		<?php } else { ?>
		<input type="submit" name="add" value="Create Notification" class="create">
		<?php } ?>
	</td>
</tr>
</table>
</form>

<hr>

<h2>Configured Notifications</h2>

<span style="display:none;" id="sort_buttons_template">
<!-- heading sort buttons -->
<span class="sort_up" title="Sort ascending">Asc</span><span class="sort_down" title="Sort descending">Desc</span>
</span>

<table class="standard standard_account_list">
<thead>
	<tr>
		<th>Notification</th>
		<th>Period</th>
		<th>Last notification</th>
		<th></th>
	</tr>
</thead>
<tbody>
<?php
$count = 0;
foreach ($notifications as $notification) {
	switch ($notification['notification_type']) {
		case "ticker":
			$q = db()->prepare("SELECT * FROM notifications_ticker WHERE id=? LIMIT 1");
			$q->execute(array($notification['type_id']));
			$account = $q->fetch();
			if (!$account) {
				throw new Exception("Could not find account '" . $notification['notification_type'] . "' for notification " . $notification['id']);
			}

			$account_text = "Exchange rate on " . get_exchange_name($account['exchange']) . " for " .
				get_currency_abbr($account['currency1']) . "/" . get_currency_abbr($account['currency2']);
			$value_label = get_currency_abbr($account['currency1']) . "/" . get_currency_abbr($account['currency2']);

			break;

		case "summary_instance":
			$q = db()->prepare("SELECT * FROM notifications_summary_instances WHERE id=? LIMIT 1");
			$q->execute(array($notification['type_id']));
			$account = $q->fetch();
			if (!$account) {
				throw new Exception("Could not find account '" . $notification['notification_type'] . "' for notification " . $notification['id']);
			}

			if (substr($account['summary_type'], 0, strlen('totalmh_')) == 'totalmh_') {
				$currency = substr($account['summary_type'], strlen('totalmh_'));
				$account_text = "My total " . get_currency_abbr($currency) . " hashrate";
				$value_label = "MH/s";
			} else if (substr($account['summary_type'], 0, strlen('total')) == 'total') {
				$currency = substr($account['summary_type'], strlen('total'));
				$account_text = "My total " . get_currency_abbr($currency);
				$value_label = get_currency_abbr($currency);
			} else if (substr($account['summary_type'], 0, strlen('all2')) == 'all2') {
				$summary_type = substr($account['summary_type'], strlen('all2'));
				$summary_types = get_total_conversion_summary_types();
				$account_text = "My converted " . $summary_types[$summary_type]['short_title'];
				$value_label = get_currency_abbr($summary_types[$summary_type]['currency']);
			} else {
				$account_text = "unknown";
				$value_label = "unknown";
			}

			break;

		default:
			throw new Exception("Unknown notification type '" . $notification['notification_type'] . "'");
	}

	$permitted = get_permitted_notification_conditions();
	switch ($notification['trigger_condition']) {
		case "increases":
		case "decreases":
			$trigger_text = $permitted[$notification['trigger_condition']];
			break;

		case "increases_by":
		case "decreases_by":
			$trigger_text = $permitted[$notification['trigger_condition']] . " " . number_format_autoprecision_html($notification['trigger_value'], $notification['is_percent'] ? '%' : (' ' . $value_label));
			break;

		case "above":
		case "below":
			$trigger_text = $permitted[$notification['trigger_condition']] . " " . number_format_autoprecision_html($notification['trigger_value'], " " . $value_label);
			break;

		default:
			throw new Exception("Unknown notification trigger '" . $notification['trigger_condition'] . "'");
	}

?>
	<tr class="<?php echo ++$count % 2 == 0 ? "odd" : "even"; ?><?php echo ($instance && $instance['id'] == $notification['id']) ? " selected" : ""; ?>">
		<td><span class="email_notification"><?php echo $account_text . " " . $trigger_text; ?></span></td>
		<td><?php $notification_periods = get_permitted_notification_periods();
			echo $notification_periods[$notification['period']]['title']; ?></td>
		<td><?php echo recent_format_html($notification['last_notification']); ?></td>
		<td class="buttons">
			<form action="<?php echo htmlspecialchars(url_for('wizard_notifications')); ?>" method="get">
				<input type="hidden" name="edit" value="<?php echo htmlspecialchars($notification['id']); ?>">
				<input type="submit" value="Edit" class="edit">
			</form>
			<form action="<?php echo htmlspecialchars(url_for('wizard_notifications_post')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($notification['id']); ?>">
				<input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm('Are you sure you want to remove this notification?');">
			</form>
		</td>
	</tr>
<?php } ?>
</tbody>
</table>

<div style="clear:both;"></div>

<div class="wizard-buttons">
<a class="button" href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">&lt; Previous</a>
<input type="submit" name="submit" value="Next &gt;">
</div>
</div>

<?php

require_template("wizard_notifications_footer");

page_footer();
