<?php

/**
 * Display information about premium accounts.
 */

require(__DIR__ . "/../inc/global.php");

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../graphs/types.php");

$messages = array();
$errors = array();

page_header("Premium Accounts", "page_premium", array('js' => 'common', 'jquery' => true));

?>

<?php require_template("premium"); ?>

<?php
$welcome = false;
require(__DIR__ . "/_premium_features.php");
?>

<p>
	You may purchase or extend your premium account by logging into your
	<a href="<?php echo htmlspecialchars(url_for('user#user_premium')); ?>">user account</a>, or
	by selecting the appropriate payment option below.
</p>

<?php
require(__DIR__ . "/_premium_prices.php");
?>

<?php
page_footer();
