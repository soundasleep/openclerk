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
		'api_username' => array('title' => 'Username', 'callback' => 'is_valid_vircurex_apiusername'),
		'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_vircurex_apisecret', 'length' => 128),
	),
	'table' => 'accounts_vircurex',
	'title' => 'Vircurex account',
	'url' => 'accounts_vircurex',
	'exchange' => 'vircurex',
);

require("_accounts_standard.php");

function accounts_standard_instructions() {
	global $account_data;
?>
	<div class="instructions_add">
	<h2>Adding a Vircurex account</h2>

	<ol class="steps">
		<li>Log into your <a href="https://mtgox.com/security">Vircurex account</a> and visit <i>Settings</i> in the top right navigation.<br>
			<img src="img/accounts/vircurex1.png"></li>

		<li>Under the <i>API tab</i>, check <i>Get balance</i>, insert in any random secret string - for example,
			you can use <a href="https://www.grc.com/passwords.htm">GRC&apos;s random password generator</a> to generate
			a 63-character random alphanumeric string - and click <i>Save</i>.<br>
			<img src="img/accounts/vircurex2.png">

		<li>Copy and paste your both your username and your chosen random secret string into the form above, and click "Add account".</li>
	</ol>
	</div>
<?php }

function accounts_standard_safety() {
	global $account_data;
?>
	<div class="instructions_safe">
	<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> your Vircurex API data?</h2>

	<ul>
		<li>You need to make sure that the API key <em>only</em> has the <i>Get balance</i> permission. This should
			mean that the API key can only be used to retrieve account status, and it should not be possible
			to perform trades or withdraw funds using that key.</li>

		<li>Your Vircurex username and secret will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
			site, even if you have logged in. Your secrets will also not be displayed on the Vircurex site.</li>

		<li>Through the Vircurex interface you can revoke an API key&apos;s access at any time by
			going to the <i>API</i> tab and either unchecking <i>Get balance</i>, or replacing the secret.</li>
	</ul>
	</div>

<?php
}

page_footer();
