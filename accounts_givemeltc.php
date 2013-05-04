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
	'table' => 'accounts_givemeltc',
	'title' => 'Give Me LTC account',
	'url' => 'accounts_givemeltc',
	'exchange' => 'givemeltc',
);

require("_accounts_standard.php");

?>

<div class="instructions_add">
<h2>Adding a Give Me LTC account</h2>

<ol class="steps">
	<li>Log into your <a href="https://give-me-ltc.com/accountdetails">Give Me LTC account details</a>.<br>
		<img src="img/accounts/givemeltc1.png"></li>

	<li>Find your <i>API Key</i>, as illustrated below: <br>
		<img src="img/accounts/givemeltc2.png"></li>

	<li>Copy and paste this <i>API Key</i> into the form above, and click "Add account".</li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a Give Me LTC API key?</h2>

<ul>
	<li>At the time of writing, a Give Me LTC API key can only be used to retrieve account balances and
		worker status; it is not possible to perform transactions or change user details using the API key.</li>

	<li>Your Give Me LTC API keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>At the time of writing, it is not possible to change or reset your Give Me LTC API key.</li>
</ul>
</div>

<?php

page_footer();
