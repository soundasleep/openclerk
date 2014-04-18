<?php

/**
 * This page is the third page in a series of wizards to configure a user account.
 * A user may revisit this page at any time to reconfigure their account.
 * This page allows the user to select how the user will define/create their graphs.
 *
 * The way that resets/update work is as follows:
 *   auto -> none		all graphs, graph pages become is_managed=0
 *   none -> auto		reset + updated
 *   auto -> managed	updated
 *   managed -> auto	reset + updated
 *   managed -> none	all graphs, graph pages become is_managed=0
 *   none -> managed	reset + updated
 */

require(__DIR__ . "/../inc/global.php");
require_login();

require(__DIR__ . "/../layout/templates.php");
page_header("Report Preferences", "page_wizard_reports", array('jquery' => true, 'js' => array('common', 'wizard'), 'class' => 'page_accounts'));

$user = get_user(user_id());
require_user($user);

$messages = array();

// get all of our accounts
$accounts = user_limits_summary(user_id());

// get our currency preferences
require(__DIR__ . "/graphs/util.php");
$summaries = get_all_summary_currencies();
$currencies = get_all_currencies();

require(__DIR__ . "/graphs/types.php");
$graphs = graph_types();

// work out which graphs we would have
require(__DIR__ . "/graphs/managed.php");
$auto_graphs = calculate_user_graphs($user, 'auto');
$managed_graphs = calculate_all_managed_graphs($user);

$managed_preferences = array();
$q = db()->prepare("SELECT * FROM managed_graphs WHERE user_id=?");
$q->execute(array(user_id()));
while ($m = $q->fetch()) {
	$managed_preferences[$m['preference']] = $m;
}

require_template("wizard_reports");

?>

<div class="wizard">

<form action="<?php echo htmlspecialchars(url_for('wizard_reports_post')); ?>" method="post">

<ul class="currency-preferences">
	<li>My preferred cryptocurrency:
		<select name="preferred_crypto">
		<?php foreach (get_all_cryptocurrencies() as $c) {
			if (isset($summaries[$c])) {
				echo "<option value=\"" . htmlspecialchars($c) . "\"
					class=\"currency_name_" . htmlspecialchars($c) . "\"" . ($user['preferred_crypto'] == $c ? " selected" : "") . ">" . get_currency_abbr($c) . "</option>\n";
			}
		} ?>
		</select>
	</li>

	<li>My preferred fiat currency:
		<select name="preferred_fiat">
		<?php foreach (get_all_currencies() as $c) {
			if (in_array($c, get_all_cryptocurrencies()))
				continue;

			if (isset($summaries[$c])) {
				echo "<option value=\"" . htmlspecialchars($c) . "\"
					class=\"currency_name_" . htmlspecialchars($c) . "\"" . ($user['preferred_fiat'] == $c ? " selected" : "") . ">" . get_currency_abbr($c) . "</option>\n";
			}
		} ?>
		</select>
	</li>
</ul>

<?php
function print_graph_types($managed, $is_auto = false) {
	global $graphs, $user;

?>
	<a class="collapse-link collapsed report-help">?</a>

	<div class="collapse-target report-help-details">
		This will display the following graphs, based on <a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>">your currencies</a> and <a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">your accounts</a>:
		<ul class="managed-graphs">
		<?php foreach ($managed as $graph_key => $graph_data) { ?>
			<li><?php echo isset($graphs[$graph_key]) ? htmlspecialchars($graphs[$graph_key]['title']) : "<i>(Unknown graph '" . htmlspecialchars($graph_key) . "')</i>"; ?>
			<?php if (is_admin()) {
				echo "<span class=\"debug\">";
				$debug = array();
				foreach ($graph_data as $data_key => $data_value) {
					$debug[] = htmlspecialchars($data_key) . " = " . implode(",", is_array($data_value) ? $data_value : array($data_value));
				}
				echo implode(", ", $debug);
				echo "</span>";
			} ?></li>
		<?php } ?>
		<?php if (!$managed) { ?>
			<li><i>(No graphs yet in this category.)</i></li>
		<?php } ?>
		<?php if ($is_auto && !$user['is_premium']) { ?>
			<li>Upgrade to a <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium account</a> to enable more automatic graphs.</li>
		<?php } ?>
		</ul>
	</div>
<?php
}
?>

<ul class="report-types">

	<li>
		<label><input type="radio" name="preference" value="auto"<?php echo require_get("preference", $user['graph_managed_type']) == 'auto' ? ' checked' : ''; ?>> Automatically select the best reports for me. (<?php echo plural(count($auto_graphs), "graph"); ?>)</label>
		<?php print_graph_types($auto_graphs, true /* is_auto */); ?>

		<?php if ($user['graph_managed_type'] != 'auto') { ?>
		<div class="reset-warning">
		Warning: Selecting this option will reset your currently defined reports and graphs (you will not lose any historical data).
		</div>
		<?php } ?>
	</li>

	<li>
		<label><input type="radio" name="preference" value="managed"<?php echo require_get("preference", $user['graph_managed_type']) == 'managed' ? ' checked' : ''; ?>> Select reports based on my portfolio preferences:</label>

		<?php if ($user['graph_managed_type'] == 'none') { ?>
		<div class="reset-warning">
		Warning: Selecting this option will reset your currently defined reports and graphs (you will not lose any historical data).
		</div>
		<?php } ?>

		<ul class="managed-types">
			<?php
			foreach (get_managed_graph_categories() as $key => $label) { ?>
			<li>
				<label><input type="checkbox" name="managed[]"
					value="<?php echo htmlspecialchars($key); ?>"<?php echo isset($managed_preferences[$key]) ? " checked" : ""; ?>> <?php echo htmlspecialchars($label); ?> (<?php echo plural(count($managed_graphs[$key]), "graph"); ?>)</label>
				<?php print_graph_types($managed_graphs[$key]); ?>
			<?php } ?>
		</ul>
	</li>

	<li>
		<label><input type="radio" name="preference" value="none"<?php echo require_get("preference", $user['graph_managed_type']) == 'none' ? ' checked' : ''; ?>> I will manage my own graphs and pages.</label>
	</li>

</ul>

<div style="clear:both;"></div>

<div class="wizard-buttons">
<a class="button" href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">&lt; Previous</a>
<input type="submit" name="submit" value="Next &gt;">
</div>
</div>

<?php

require_template("wizard_reports_footer");

page_footer();
