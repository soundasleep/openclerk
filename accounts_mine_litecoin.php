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
		'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mmcfe_apikey'),
	),
	'table' => 'accounts_mine_litecoin',
	'title' => 'Mine-Litecoin account',
	'url' => 'accounts_mine_litecoin',
	'exchange' => 'mine_litecoin',
	'khash' => true,
);

require("_accounts_standard.php");

?>

<div class="instructions_add">
<h2>Adding a Mine-Litecoin account</h2>

<ol class="steps">
	<li>Log into your <a href="https://www.mine-litecoin.com/accountdetails">Mine-Litecoin account details</a>.<br>
		<img src="img/accounts/mine-litecoin1.png"></li>

	<li>Find your <i>API Key</i>, as illustrated below: <br>
		<img src="img/accounts/mine-litecoin2.png"></li>

	<li>Copy and paste this <i>API Key</i> into the form above, and click "Add account".</li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a Mine-Litecoin API key?</h2>

<ul>
	<li>At the time of writing, a Mine-Litecoin API key can only be used to retrieve account balances and
		worker status; it is not possible to perform transactions or change user details using the API key.</li>

	<li>Your Mine-Litecoin API keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>At the time of writing, it is not possible to change or reset your Mine-Litecoin API key.</li>
</ul>
</div>

<?php

page_footer();
