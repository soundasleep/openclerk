<?php

require("inc/global.php");
require_login();

require("layout/templates.php");

$user = get_user(user_id());
require_user($user);

$messages = array();
$errors = array();

$account_data = array(
	'inputs' => array(
		'api_key' => array('title' => 'API key', 'callback' => 'is_valid_btcguild_apikey'),
	),
	'table' => 'accounts_btcguild',
	'url' => 'accounts_btcguild',
	'exchange' => 'btcguild',
);

require("_accounts_standard.php");

function accounts_standard_instructions() {
	global $account_data;
?>
	<div class="instructions_add">
	<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

	<ol class="steps">
		<li>Log into your <?php echo $account_data['exchange_name']; ?> account
			and visit your <a href="https://www.btcguild.com/index.php?page=account">settings page</a>.<br>
			<img src="img/accounts/btcguild1.png"></li>

		<li>Copy and paste your <i>API Key</i> into the "Add new" form, and click "Add account".<br>
			<img src="img/accounts/btcguild2.png"></li>
	</ol>
	</div>
<?php }

function accounts_standard_safety() {
	global $account_data;
?>
	<div class="instructions_safe">
	<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

	<ul>
		<li>At the time of writing, a <?php echo $account_data['exchange_name']; ?> API key can only be used to retrieve account balances and worker status;
			it should not be possible to perform transactions or change user details using the API key.</li>

		<li>Your <?php echo $account_data['exchange_name']; ?> API keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
			site, even if you have logged in.</li>

		<li>At the time of writing, it is not possible to change or reset your <?php echo $account_data['exchange_name']; ?> API key.</li>
	</ul>
	</div>
<?php }

page_footer();
