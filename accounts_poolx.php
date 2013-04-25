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
		'api_key' => array('title' => 'API key', 'callback' => 'is_valid_poolx_apikey'),
	),
	'table' => 'accounts_poolx',
	'title' => 'Pool-x account',
	'url' => 'accounts_poolx',
	'exchange' => 'poolx',
);

require("_accounts_standard.php");

?>

<div class="instructions_add">
<h2>Adding a Pool-x.eu account</h2>

<ol class="steps">
	<li>Log into your <a href="http://pool-x.eu/accountdetails">Pool-x.eu account details</a>.</li>

	<li>Find your <i>API Key</i>, as illustrated below: <br>
	<img src="img/accounts/poolx.png"></li>

	<li>Copy and paste this <i>API Key</i> into the form above, and click "Add account".</li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a Pool-x.eu API key?</h2>

<ul>
	<li>At the time of writing, a Pool-x.eu API key can only be used to retrieve account balances and
		worker status; it is not possible to perform transactions or change user details using the API key.</li>

	<li>At the time of writing, it is not possible to change or reset your Pool-x.eu API key.</li>
</ul>
</div>

<?php

page_footer();
