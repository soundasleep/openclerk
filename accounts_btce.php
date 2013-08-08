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
		'api_key' => array('title' => 'API key', 'callback' => 'is_valid_btce_apikey'),
		'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_btce_apisecret'),
	),
	'table' => 'accounts_btce',
	'title' => 'BTC-e account',
	'url' => 'accounts_btce',
	'exchange' => 'btce',
);

require("_accounts_standard.php");

function accounts_standard_instructions() {
	global $account_data;
?>
<div class="instructions_add">
<h2>Adding a BTC-e account</h2>

<ol class="steps">
	<li>Log into your <a href="https://btc-e.com/profile">BTC-e profile</a>.<br>
	<img src="img/accounts/btce1.png"></li>

	<li>From the profile, select <i>API keys</i>.<br>
	<img src="img/accounts/btce2.png"></li>

	<li>Create a name for a new key (such as "<?php echo htmlspecialchars(get_site_config('site_name')); ?>") and click <i>Create</i>.<br>
	<img src="img/accounts/btce3.png"></li>

	<li>Select the <i>info</i> permission, and click <i>Save</i>. Once the key has been saved, copy and paste the <i>Key</i> and <i>Secret</i> into the form above, and click "Add account".<br>
	<img src="img/accounts/btce4.png"></li>
</ol>
</div>
<?php }

function accounts_standard_safety() {
	global $account_data;
?>
<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a BTC-e API key?</h2>

<ul>
	<li>You need to make sure that the API key <em>only</em> has the <i>info</i> permission. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your BTC-e keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the BTC-e interface you can revoke an API key&apos;s access at any time by clicking <i>Disable</i>.</li>
</ul>
</div>

<?php
}

page_footer();
