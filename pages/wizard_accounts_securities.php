<?php

/**
 * This page is the second page in a series of wizards to configure a user account.
 * A user may revisit this page at any time to reconfigure their account.
 * This page allows the user to select which kind of accounts to add.
 */

require_login();

require(__DIR__ . "/../graphs/util.php");

page_header(t("Add Securities Exchanges"), "page_wizard_accounts_securities", array('js' => array('accounts', 'wizard'), 'class' => 'page_accounts wizard_page'));

global $user;
$user = get_user(user_id());
require_user($user);

$messages = array();

// get all of our accounts
global $accounts;
$accounts = user_limits_summary(user_id());

global $account_type;
$account_type = get_wizard_account_type('securities');
require_template("wizard_accounts_securities");

?>

<div class="wizard">

<?php
require(__DIR__ . "/_wizard_accounts.php");
?>

<div class="wizard-buttons">
<a class="button" href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>"><?php echo ht("< Previous"); ?></a>
</div>
</div>

<?php

require_template("wizard_accounts_securities_footer");

page_footer();
