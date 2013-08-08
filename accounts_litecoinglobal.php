<?php

require("inc/global.php");
require_login();

require("layout/templates.php");

$user = get_user(user_id());
if (!$user) {
	throw new Exception("Could not find self user.");
}

$messages = array();
$errors = array();

$account_data = array(
	'inputs' => array(
		'api_key' => array('title' => 'Read-Only API key', 'callback' => 'is_valid_litecoinglobal_apikey'),
	),
	'table' => 'accounts_litecoinglobal',
	'title' => 'Litecoin Global account',
	'url' => 'accounts_litecoinglobal',
	'exchange' => 'litecoinglobal',
);

require("_accounts_standard.php");

function accounts_standard_instructions() {
	global $account_data;
?>
	<div class="instructions_add">
	<h2>Adding a Litecoin Global account</h2>

	<ol class="steps">
		<li>Log into your <a href="https://www.litecoinglobal.com/account">Litecoin Global account</a> and visit your <i>Account</i>.<br>
			<img src="img/accounts/litecoinglobal1.png"></li>

		<li>In the <i>Account Information</i> tab, copy and paste the <i>Read-Only API Key</i> into the form above, and click "Add account".<br>
			<img src="img/accounts/litecoinglobal2.png"></li>
	</ol>
	</div>
<?php }

function accounts_standard_safety() {
	global $account_data;
?>
	<div class="instructions_safe">
	<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a Litecoin Global Read-Only API key?</h2>

	<ul>
		<li>The API key that you provide is a read-only API key. This should
			mean that the API key can only be used to retrieve account status, and it should not be possible
			to perform trades or withdraw funds using that key.</li>

		<li>Your Litecoin Global keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
			site, even if you have logged in.</li>

		<li>At the time of writing, it is not possible to change or reset your Litecoin Global Read-Only API key.</li>
	</ul>
	</div>

<?php
}

page_footer();
