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
	'table' => 'accounts_miningforeman',
	'title' => 'Mining Foreman account',
	'url' => 'accounts_miningforeman',
	'exchange' => 'miningforeman',
	'khash' => true,
);

require("_accounts_standard.php");

?>

<div class="instructions_add">
<h2>Adding a Mining Foreman account</h2>

<ol class="steps">
	<li>Log into your <a href="http://www.mining-foreman.org/accountdetails">Mining Foreman account details</a>.<br>
		<img src="img/accounts/miningforeman1.png"></li>

	<li>Find your <i>API Key</i>, as illustrated below: <br>
		<img src="img/accounts/miningforeman2.png"></li>

	<li>Copy and paste this <i>API Key</i> into the form above, and click "Add account".</li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a Mining Foreman API key?</h2>

<ul>
	<li>At the time of writing, a Mining Foreman API key can only be used to retrieve account balances and
		worker status; it is not possible to perform transactions or change user details using the API key.</li>

	<li>Your Mining Foreman API keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>At the time of writing, it is not possible to change or reset your Mining Foreman API key.</li>
</ul>
</div>

<?php

page_footer();
