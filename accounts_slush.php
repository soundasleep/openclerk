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
		'api_token' => array('title' => 'API current token', 'callback' => 'is_valid_slush_apitoken'),
	),
	'table' => 'accounts_slush',
	'title' => 'Slush\'s pool account',
	'url' => 'accounts_slush',
	'exchange' => 'slush',
);

require("_accounts_standard.php");

function accounts_standard_instructions() {
	global $account_data;
?>
	<div class="instructions_add">
	<h2>Adding a Slush&apos;s pool account</h2>

	<ol class="steps">
		<li>Log into your <a href="https://mining.bitcoin.cz/accounts/profile/">Slush&apos;s pool account details</a>, and select
			the <i>Manage API tokens</i> tab.<br>
			<img src="img/accounts/slush1.png"></li>

		<li>Find your <i>current token</i>, as illustrated below: <br>
			<img src="img/accounts/slush2.png"></li>

		<li>Copy and paste this <i>current token</i> into the form above, and click "Add account".</li>
	</ol>
	</div>
<?php }

function accounts_standard_safety() {
	global $account_data;
?>
	<div class="instructions_safe">
	<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a Slush&apos;s pool API key?</h2>

	<ul>
		<li>At the time of writing, a Slush&apos;s pool API key can only be used to retrieve account balances and
			worker status; it is not possible to perform transactions or change user details using the API key.</li>

		<li>Your Slush&apos;s pool API keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
			site, even if you have logged in.</li>

		<li>You may generate a new API token at any time by visiting your <i>Manage API Tokens</i> page, which will
			revoke any existing access.</li>
	</ul>
	</div>
<?php }

page_footer();
