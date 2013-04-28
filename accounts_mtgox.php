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
		'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mtgox_apikey'),
		'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_mtgox_apisecret', 'length' => 128),
	),
	'table' => 'accounts_mtgox',
	'title' => 'Mt.Gox account',
	'url' => 'accounts_mtgox',
	'exchange' => 'mtgox',
);

require("_accounts_standard.php");

?>

<div class="instructions_add">
<h2>Adding a Mt.Gox account</h2>

<ol class="steps">
	<li>Log into your <a href="https://mtgox.com/security">Mt.Gox account</a> and visit the <i>security center</i>.<br>
		<img src="img/accounts/mtgox1.png"></li>

	<li>Under <i>Advanced API Key Creation</i>, create a name for a new key (such as "<?php echo htmlspecialchars(get_site_config('site_name')); ?>");
		give the key <i>Get Info</i> rights; and click <i>Create</i>.<br>
		<img src="img/accounts/mtgox2.png"></li>

	<li>Copy and paste the <i>API Key</i> and <i>Secret</i> into the form above, and click "Add account". Click "Update" on the Mt.Gox site to save your changes.<br>
		<img src="img/accounts/mtgox3.png"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a Mt.Gox API key?</h2>

<ul>
	<li>You need to make sure that the API key <em>only</em> has the <i>Get Info</i> permission. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your Mt.Gox keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the Mt.Gox interface you can revoke an API key&apos;s access at any time by
		going to <i>Current API Keys</i> and clicking on the red <i>Delete</i> icon.</li>
</ul>
</div>

<?php


page_footer();
