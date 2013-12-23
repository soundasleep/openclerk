<?php

// we will have set $account_type already
if (!isset($account_type)) {
	throw new Exception("account_type needs to be set");
}

// get all of our accounts
$accounts = array();
$add_types = array();
$add_type_names = array();
$previous_data = isset($_SESSION['wizard_data']) ? $_SESSION['wizard_data'] : array();
unset($_SESSION['wizard_data']);

foreach (account_data_grouped() as $label => $data) {
	foreach ($data as $key => $value) {
		if (isset($value['wizard']) && $value['wizard'] == $account_type['wizard']) {
			// we've found a valid account type
			$account_data = get_accounts_wizard_config($key);
			if (!(isset($value['disabled']) && $value['disabled'])) {
				$add_types[] = $key;
				$add_type_names[$key] = get_exchange_name($key) . (isset($value['suffix']) ? $value['suffix'] : "");
			}

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
<?php echo $account_type['accounts']; ?> should be updated
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
		<th class="buttons"></th>
	</tr>
</thead>
<tbody>
<?php
// uses $accounts to generate rows;
// this is in an include so we can also use it in wizard_accounts_callback
require(__DIR__ . "/_wizard_accounts_rows.php");
?>
</tbody>
</table>

<div class="columns2">
<div class="column">

<h2>Add new <?php echo htmlspecialchars($account_type['title']); ?></h2>

<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_post')); ?>" method="post" class="wizard-add-account">
	<table class="standard" id="wizard_account_table">
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
	<tr class="buttons">
		<td colspan="2" class="buttons">
			<input type="submit" name="add" value="Add account" class="add">
			<input type="hidden" name="callback" value="<?php echo htmlspecialchars($account_type['url']); ?>">

			<?php if (isset($account_type['add_help'])) { ?>
			<div class="help">
				<a href="<?php echo htmlspecialchars(url_for('kb', array('q' => $account_type['add_help']))); ?>">Add <?php echo htmlspecialchars($account_type['a']); ?> <?php echo htmlspecialchars($account_type['title']); ?> not listed here</a>
			</div>
			<?php } ?>
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
function previous_data() {
	return <?php echo json_encode($previous_data); ?>;
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