<?php

/**
 * This page is the second page in a series of wizards to configure a user account.
 * A user may revisit this page at any time to reconfigure their account.
 * This page allows the user to select which kind of accounts to add.
 */

require(__DIR__ . "/../inc/global.php");
require_login();

require(__DIR__ . "/../graphs/util.php");

require(__DIR__ . "/../layout/templates.php");
page_header(t("Add Exchanges"), "page_wizard_accounts_exchanges", array('js' => array('accounts', 'wizard'), 'class' => 'page_accounts wizard_page'));

$user = get_user(user_id());
require_user($user);

$messages = array();

$account_type = get_wizard_account_type('exchanges');
require_template("wizard_accounts_exchanges");

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

require_template("wizard_accounts_exchanges_footer");

page_footer();
