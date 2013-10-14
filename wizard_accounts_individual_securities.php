<?php

/**
 * This page is the second page in a series of wizards to configure a user account.
 * A user may revisit this page at any time to reconfigure their account.
 * This page allows the user to select which kind of accounts to add.
 */

require(__DIR__ . "/inc/global.php");
require_login();

require(__DIR__ . "/graphs/util.php");

require(__DIR__ . "/layout/templates.php");
page_header("Add Individual Securities", "page_wizard_accounts_individual_securities", array('jquery' => true, 'js' => array('accounts', 'wizard'), 'common_js' => true, 'class' => 'page_accounts'));

$user = get_user(user_id());
require_user($user);

$messages = array();

// get all of our accounts
$accounts = user_limits_summary(user_id());

$account_type = array(
	'title' => 'Individual Security',
	'titles' => 'Individual Securities',
	'accounts' => 'securities',
	'wizard' => 'individual',
	'hashrate' => false,
	'url' => 'wizard_accounts_individual_securities',
	'first_heading' => 'Exchange',
	'display_headings' => array('Security', 'Quantity'),
	'display_callback' => 'get_individual_security_config',
);

function get_individual_security_config($account) {
	$security = "(unknown exchange)";
	$securities = false;
	$historical_key = false;
	switch ($account['exchange']) {
		case "individual_litecoinglobal":
			$securities = dropdown_get_litecoinglobal_securities();
			$historical_key = 'securities_litecoinglobal_ltc';
			break;
		case "individual_btct":
			$securities = dropdown_get_btct_securities();
			$historical_key = 'securities_btct_btc';
			break;
		case "individual_bitfunder":
			$securities = dropdown_get_bitfunder_securities();
			$historical_key = 'securities_bitfunder_btc';
			break;
		case "individual_havelock":
			$securities = dropdown_get_havelock_securities();
			$historical_key = 'securities_havelock_btc';
			break;
		case "individual_cryptostocks":
			$securities = dropdown_get_cryptostocks_securities();
			break;
	}

	if ($securities) {
		if (isset($securities[$account['security_id']])) {
			if ($historical_key) {
				$security = "<a href=\"" . htmlspecialchars(url_for('historical', array('id' => $historical_key, 'days' => 180, 'name' => $securities[$account['security_id']]))) . "\">" . htmlspecialchars($securities[$account['security_id']]) . "</a>";
			} else {
				$security = htmlspecialchars($securities[$account['security_id']]);
			}
		} else {
			$security = "(unknown security " . htmlspecialchars($account['security_id']) . ")";
		}
	}

	return array(
		$security,
		number_format($account['quantity']),
	);
}

require_template("wizard_accounts_individual_securities");

?>

<div class="wizard">

<?php
require(__DIR__ . "/_wizard_accounts.php");
?>

<div class="wizard-buttons">
<a class="button" href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">&lt; Previous</a>
</div>
</div>

<?php

require_template("wizard_accounts_individual_securities_footer");

page_footer();
