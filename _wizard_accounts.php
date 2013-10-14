<?php

// we will have set $account_type already
if (!isset($account_type)) {
	throw new Exception("account_type needs to be set");
}
if (!isset($account_type['display_headings'])) {
	$account_type['display_headings'] = array();
}
if (!isset($account_type['display_callback'])) {
	$account_type['display_callback'] = false;
}
if (!isset($account_type['first_heading'])) {
	$account_type['first_heading'] = $account_type['title'];
}

// get all of our accounts
$accounts = array();
$add_types = array();
$add_type_names = array();

foreach (account_data_grouped() as $label => $data) {
	foreach ($data as $key => $value) {
		if (isset($value['wizard']) && $value['wizard'] == $account_type['wizard']) {
			// we've found a valid account type
			$account_data = get_accounts_wizard_config($key);
				$add_types[] = $key;
				$add_type_names[$key] = get_exchange_name($key) . (isset($value['suffix']) ? $value['suffix'] : "");

				$q = db()->prepare("SELECT * FROM " . $account_data['table'] . "
					WHERE user_id=? ORDER BY title ASC");
				$q->execute(array(user_id()));
				while ($r = $q->fetch()) {
					$r['exchange'] = $key;
					$r['khash'] = $account_data['khash'];
				$accounts[] = $r;
			}
		}
	}
}

// sort add_types by name
function _sort_by_exchange_name($a, $b) {
	global $add_type_names;
	return strcmp(strtolower($add_type_names[$a]), strtolower($add_type_names[$b]));
}
usort($add_types, '_sort_by_exchange_name');

$account_data = null;

?>

<div class="page_accounts">

<p>
<?php
// is this user a new user?
$user['is_new'] = get_site_config('new_user_premium_update_hours') && strtotime($user['created_at']) > strtotime('-' . get_site_config('new_user_premium_update_hours') . ' hour');
?>
As a <?php echo $user['is_premium'] ? "premium user" : ($user['is_new'] ? "new user" : "<a href=\"" . htmlspecialchars(url_for('premium')) . "\">free user</a>"); ?>, your
accounts should be updated
at least once every <?php echo plural($user['is_new'] ? get_site_config('refresh_queue_hours_premium') : get_premium_value($user, "refresh_queue_hours"), 'hour');
if ($user['is_new'] && !$user['is_premium']) echo " (for the next " . plural(
	(int) (get_site_config('new_user_premium_update_hours') - ((time() - strtotime($user['created_at']))) / (60 * 60))
	, "hour") . ")"; ?>.
</p>

<h2>Your <?php echo htmlspecialchars($account_type['titles']); ?></h2>

<span style="display:none;" id="sort_buttons_template">
<!-- heading sort buttons -->
<span class="sort_up" title="Sort ascending">Asc</span><span class="sort_down" title="Sort descending">Desc</span>
</span>

<table class="standard standard_account_list">
<thead>
	<tr>
		<th><?php echo htmlspecialchars($account_type['first_heading']); ?></th>
		<th>Title</th>
		<?php foreach ($account_type['display_headings'] as $value) { ?>
			<th><?php echo htmlspecialchars($value); ?></th>
		<?php } ?>
		<th>Added</th>
		<th>Last checked</th>
		<th>Balances</th>
		<?php if ($account_type['hashrate']) { echo "<th>Hashrate</th>"; } ?>
		<th></th>
	</tr>
</thead>
<tbody>
<?php
$count = 0;
foreach ($accounts as $a) {
	$count++;
	$balances = array();
	$balances_wallet = array();
	$balances_securities = array();
	$last_updated = null;
	$job = false;

	// an account may have multiple currency balances
	$q = db()->prepare("SELECT balances.* FROM balances WHERE user_id=? AND account_id=? AND (exchange=? OR exchange=? OR exchange=?) AND is_recent=1 ORDER BY currency ASC");
		$q->execute(array(user_id(), $a['id'], $a['exchange'], $a['exchange'] . "_wallet", $a['exchange'] . "_securities"));
		while ($balance = $q->fetch()) {
			switch ($balance['exchange']) {
				case $a['exchange']:
					$balances[$balance['currency']] = $balance['balance'];
					break;
				case $a['exchange'] . "_wallet":
					$balances_wallet[$balance['currency']] = $balance['balance'];
					break;
				case $a['exchange'] . "_securities":
					$balances_securities[$balance['currency']] = $balance['balance'];
					break;
				default:
					echo "Unknown exchange '" . htmlspecialchars($balance['exchange']) . "'";
			}
			$last_updated = $balance['created_at'];
		}

		// was the last request successful?
		$q = db()->prepare("SELECT jobs.*, uncaught_exceptions.message FROM jobs
			LEFT JOIN uncaught_exceptions ON uncaught_exceptions.job_id=jobs.id
			WHERE user_id=? AND arg_id=? AND job_type=? AND is_executed=1
		ORDER BY jobs.id DESC LIMIT 1");
	$q->execute(array(user_id(), $a['id'], $a['exchange']));
	$job = $q->fetch();
	if (!$last_updated && $job) {
		$last_updated = $job['executed_at'];
	}

	$extra_display = array();
	if ($account_type['display_callback']) {
		$c = $account_type['display_callback'];
		$extra_display = $c($a);
	}
?>
	<tr class="<?php echo $count % 2 == 0 ? "odd" : "even"; ?>">
		<td><?php echo htmlspecialchars(get_exchange_name($a['exchange'])); ?></td>
		<td id="account<?php echo htmlspecialchars($a['id']); ?>" class="title">
			<span><?php echo $a['title'] ? htmlspecialchars($a['title']) : "<i>untitled</i>"; ?></span>
			<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_post')); ?>" method="post" style="display:none;">
			<input type="text" name="title" value="<?php echo htmlspecialchars($a['title']); ?>">
			<input type="submit" value="Update Title">
			<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
			<input type="hidden" name="type" value="<?php echo htmlspecialchars($a['exchange']); ?>">
			<input type="hidden" name="callback" value="<?php echo htmlspecialchars($account_type['url']); ?>">
			</form>
		</td>
		<?php foreach ($extra_display as $value) { ?>
			<td><?php echo $value; ?></td>
		<?php } ?>
		<td><?php echo recent_format_html($a['created_at']); ?></td>
		<td<?php if ($job) echo " class=\"" . ($job['is_error'] ? "job_error" : "job_success") . "\""; ?>>
			<?php echo recent_format_html($last_updated); ?>
			<?php if ($job['message']) { ?>
			: <?php echo htmlspecialchars($job['message']); ?>
			<?php } ?>
		</td>
		<td><?php
			$had_balance = false;
			echo "<ul>";
			foreach ($balances as $c => $value) {
				if ($value != 0) {
					$had_balance = true;
					echo "<li>" . currency_format($c, $value, 4) . "</li>\n";
				}
			}
			foreach ($balances_wallet as $c => $value) {
				if ($value != 0) {
					$had_balance = true;
					echo "<li>" . currency_format($c, $value, 4) . " (wallet)</li>\n";
				}
			}
			foreach ($balances_securities as $c => $value) {
				if ($value != 0) {
					$had_balance = true;
					echo "<li>" . currency_format($c, $value, 4) . " (securities)</li>\n";
				}
			}
			echo "</ul>";
			if (!$had_balance) echo "<i>None</i>";
		?></td>
		<?php if ($account_type['hashrate']) {
			$q = db()->prepare("SELECT * FROM hashrates WHERE exchange=? AND account_id=? AND user_id=? AND is_recent=1 LIMIT 1");
			$q->execute(array($a['exchange'], $a['id'], $a['user_id']));
			echo "<td>";
			if ($mhash = $q->fetch()) {
				echo $mhash['mhash'] ? (!isset($a['khash']) ? number_format_autoprecision($mhash['mhash'], 1) . " MH/s" : number_format_autoprecision($mhash['mhash'] * 1000, 1) . " KH/s") : "-";
			} else {
				echo "-";
			}
			echo "</td>";
		} ?>
		<td>
			<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_post')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
				<input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm('Are you sure you want to remove this account?');">
				<input type="hidden" name="type" value="<?php echo htmlspecialchars($a['exchange']); ?>">
				<input type="hidden" name="callback" value="<?php echo htmlspecialchars($account_type['url']); ?>">
			</form>
		</td>
	</tr>
<?php } ?>
<?php if (!$accounts) { ?>
	<tr><td colspan="<?php echo 7 + count($account_type['display_headings']); ?>"><i>(No accounts defined.)</i></td></tr>
<?php } ?>
</tbody>
</table>

<div class="columns2">
<div class="column">

<h2>Add new <?php echo htmlspecialchars($account_type['title']); ?></h2>

<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_post')); ?>" method="post" class="wizard-add-account">
	<table class="standard">
	<tr>
		<th><label for="type"><?php echo htmlspecialchars($account_type['first_heading']); ?>:</label></th>
		<td>
			<select id="type" name="type">
			<?php foreach ($add_types as $exchange) {
				echo "<option value=\"" . htmlspecialchars($exchange) . "\"" . ($exchange == require_get("exchange", false) ? " selected" : "")  . ">";
				echo htmlspecialchars($add_type_names[$exchange]);
				echo "</option>\n";
			} ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><label for="title">Title:</label></th>
		<td><input id="title" type="text" name="title" size="18" maxlength="64" value="<?php echo htmlspecialchars(require_get("title", "")); ?>"> (optional)</td>
	</tr>
	<tr id="add_account_template" style="display:none;">
		<th><label for="ignored">Parameter:</label></th>
		<td><input id="ignored" type="text" name="ignored" size="18" maxlength="64" value=""></td>
	</tr>
	<tr id="add_account_template_dropdown" style="display:none;">
		<th><label for="ignored">Parameter:</label></th>
		<td><select id="ignored" name="ignored">
			<option id="option_template"></option>
		</select></td>
	</tr>
	<tr>
		<td colspan="2" class="buttons">
			<input type="submit" name="add" value="Add account" class="add">
			<input type="hidden" name="callback" value="<?php echo htmlspecialchars($account_type['url']); ?>">
		</td>
	</tr>
	</table>
</form>

<script type="text/javascript">
function available_exchanges() {
	return [
<?php foreach ($add_types as $exchange) {
	$config = get_accounts_wizard_config($exchange);
	echo "{ 'exchange' : " . json_encode($exchange) . ", \n";
	echo " 'inputs' : [";
	foreach ($config['inputs'] as $key => $input) {
		echo "{ 'key': " . json_encode($key) . ", 'title' : " . json_encode($input['title']);
		if (isset($input['dropdown']) && $input['dropdown']) {
			$callback = $input['dropdown'];
			echo ", 'dropdown' : " . json_encode($callback());
		}
		if (isset($input['style_prefix']) && $input['style_prefix']) {
			echo ", 'style_prefix' : " . json_encode($input['style_prefix']);
		}
		echo ", 'length' : " . json_encode(isset($input['length']) ? $input['length'] : 64) . "},";
	}
	echo "],";
	echo "},\n";
} ?>
	];
}
</script>

</div>
<div class="column">
<h2>Help</h2>

<div id="accounts_help_target">Select an exchange to display help...</div>

</div>
</div>

<div style="display:none;" id="accounts_help">
<?php foreach ($add_types as $exchange) { ?>
	<div id="accounts_help_<?php echo htmlspecialchars($exchange); ?>">
	<?php require_template("inline_accounts_" . $exchange); ?>
	<span class="more_help"><a href="<?php echo htmlspecialchars(url_for('kb', array('q' => $exchange))); ?>">More help</a></span>
	</div>
<?php } ?>
</div>

<div style="clear:both;"></div>

</div>