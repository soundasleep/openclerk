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
		'api_key' => array('title' => 'API key', 'callback' => 'is_valid_50btc_apikey'),
	),
	'table' => 'accounts_50btc',
	'url' => 'accounts_50btc',
	'exchange' => '50btc',
);

require("_accounts_standard.php");

?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <?php echo $account_data['exchange_name']; ?> account
		and visit your <a href="https://50btc.com/en/worker/stats">Mining page</a>.<br>
		<img src="img/accounts/50btc1.png"></li>

	<li>Copy and paste your <i>API Key</i> into the form above, and click "Add account".<br>
		<img src="img/accounts/50btc2.png"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li>At the time of writing, a <?php echo $account_data['exchange_name']; ?> API key can only be used to retrieve account balances and worker status;
		it should not be possible to perform transactions or change user details using the API key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> API keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can revoke an API key&apos;s access at any time, by
		visiting the <a href="https://50btc.com/en/account/api">Mining API page</a> and clicking on <i>Generate new key</i>.
</ul>
</div>

<?php

page_footer();
