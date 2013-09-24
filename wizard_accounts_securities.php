<?php

/**
 * This page is the second page in a series of wizards to configure a user account.
 * A user may revisit this page at any time to reconfigure their account.
 * This page allows the user to select which kind of accounts to add.
 */

require("inc/global.php");
require_login();

require("graphs/util.php");

require("layout/templates.php");
page_header("Add Securities Exchanges", "page_wizard_accounts_securities", array('jquery' => true, 'js' => array('accounts', 'wizard'), 'common_js' => true));

$user = get_user(user_id());
require_user($user);

$messages = array();

require_template("wizard_accounts_securities");

?>

<div class="wizard">

<?php
$account_type = array(
	'title' => 'Securities Exchange',
	'titles' => 'Securities Exchanges',
	'wizard' => 'securities',
	'hashrate' => false,
	'url' => 'wizard_accounts_securities',
);

require("_wizard_accounts.php");
?>

<div class="wizard-buttons">
<a class="button" href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">&lt; Previous</a>
</div>
</div>

<?php

require_template("wizard_accounts_securities_footer");

page_footer();
