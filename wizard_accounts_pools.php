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
page_header("Add Mining Pools", "page_wizard_accounts_pools", array('jquery' => true, 'js' => array('accounts', 'wizard'), 'common_js' => true, 'class' => 'page_accounts'));

$user = get_user(user_id());
require_user($user);

$messages = array();

$account_type = array(
	'title' => 'Mining Pool',
	'titles' => 'Mining Pools',
	'wizard' => 'pools',
	'hashrate' => true,
	'url' => 'wizard_accounts_pools',
);

require_template("wizard_accounts_pools");

?>

<div class="wizard">

<?php
require("_wizard_accounts.php");
?>

<div class="wizard-buttons">
<a class="button" href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">&lt; Previous</a>
</div>
</div>

<?php

require_template("wizard_accounts_pools_footer");

page_footer();
