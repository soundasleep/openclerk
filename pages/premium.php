<?php

/**
 * Display information about premium accounts.
 */

require(__DIR__ . "/../graphs/types.php");

$messages = array();
$errors = array();

page_header(t("Premium Accounts"), "page_premium");

?>

<?php require_template("premium"); ?>

<?php
$welcome = false;
require(__DIR__ . "/_premium_features.php");
?>

<p>
	<?php echo t("You may purchase or extend your premium account by logging into your :user_account, or
	by selecting the appropriate payment option below.",
		array(
			':user_account' => link_to(url_for('user#user_premium'), t("user account")),
		)); ?>
</p>

<?php
require(__DIR__ . "/_premium_prices.php");
?>

<?php
page_footer();
