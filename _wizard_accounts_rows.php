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
			WHERE user_id=? AND arg_id=? AND job_type=? AND is_executed=1 AND jobs.is_recent=1
		ORDER BY jobs.id DESC LIMIT 1");
	$q->execute(array(user_id(), $a['id'], $a['exchange']));
	$job = $q->fetch();
	if (!$last_updated && $job) {
		$last_updated = $job['executed_at'];
	}

	// are we currently awaiting for a test callback?
	$q = db()->prepare("SELECT * FROM jobs WHERE user_id=? AND arg_id=? AND job_type=? AND is_executed=0 AND is_test_job=1 LIMIT 1");
	$q->execute(array(user_id(), $a['id'], $a['exchange']));
	$is_test_job = $q->fetch();

	$extra_display = array();
	if ($account_type['display_callback']) {
		$c = $account_type['display_callback'];
		$extra_display = $c($a);
	}

	$row_element_id = "row_" . $a['exchange'] . "_" . $a['id'];
	$is_disabled = isset($a['is_disabled']) && $a['is_disabled'];
?>
<?php if (!isset($is_in_callback)) { ?>
	<tr class="<?php echo $count % 2 == 0 ? "odd" : "even"; echo $is_disabled ? " disabled": ""; ?>" id="<?php echo htmlspecialchars($row_element_id); ?>">
<?php } ?>
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
			if ($is_disabled) echo " <i>(disabled)</i>";
		?></td>
		<?php if ($account_type['hashrate']) {
			$q = db()->prepare("SELECT * FROM hashrates WHERE exchange=? AND account_id=? AND user_id=? AND is_recent=1 LIMIT 1");
			$q->execute(array($a['exchange'], $a['id'], $a['user_id']));
			echo "<td>";
			if ($mhash = $q->fetch()) {
				echo $mhash['mhash'] ? (!(isset($a['khash']) && $a['khash']) ? number_format_autoprecision($mhash['mhash'], 1) . " MH/s" : number_format_autoprecision($mhash['mhash'] * 1000, 1) . " KH/s") : "-";
			} else {
				echo "-";
			}
			echo "</td>";
		} ?>
		<td class="buttons">
			<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_post')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
				<input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm('Are you sure you want to remove this account?');">
				<input type="hidden" name="type" value="<?php echo htmlspecialchars($a['exchange']); ?>">
				<input type="hidden" name="callback" value="<?php echo htmlspecialchars($account_type['url']); ?>">
			</form>
			<?php if ($is_test_job) { ?>
			<span class="status_loading">Testing...</span>
				<?php if (!isset($is_in_callback)) { ?>
					<script type="text/javascript">
					$(document).ready(function() {
						initialise_wizard_test_callback($('#<?php echo htmlspecialchars($row_element_id); ?>'), <?php echo json_encode(url_for('wizard_accounts_callback', array('exchange' => $a['exchange'], 'id' => $a['id']))); ?>);
					});
					</script>
				<?php } ?>
			<?php } else { ?>
			<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_post')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
				<input type="submit" name="test" value="Test" class="test">
				<input type="hidden" name="type" value="<?php echo htmlspecialchars($a['exchange']); ?>">
				<input type="hidden" name="callback" value="<?php echo htmlspecialchars($account_type['url']); ?>">
			</form>
			<?php } ?>
			<?php if ($is_disabled) { ?>
			<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_post')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id']); ?>">
				<input type="submit" name="enable" value="Enable" class="enable">
				<input type="hidden" name="type" value="<?php echo htmlspecialchars($a['exchange']); ?>">
				<input type="hidden" name="callback" value="<?php echo htmlspecialchars($account_type['url']); ?>">
			</form>
			<?php } ?>
		</td>
<?php if (!isset($is_in_callback)) { ?>
	</tr>
<?php } ?>
<?php } ?>
<?php if (!$accounts) { ?>
	<tr><td colspan="<?php echo 7 + count($account_type['display_headings']); ?>"><i>(No <?php echo $account_type['accounts']; ?> defined.)</i></td></tr>
<?php } ?>