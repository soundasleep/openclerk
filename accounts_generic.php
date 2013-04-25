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
		'api_url' => array('title' => 'URL', 'callback' => 'is_valid_generic_url'),
		'currency' => array('title' => 'Currency', 'dropdown' => 'dropdown_currency_list', 'callback' => 'is_valid_currency'),
	),
	'display' => array(
		'currency' => array('title' => 'Currency', 'format' => 'strtoupper'),
	),
	'table' => 'accounts_generic',
	'title' => 'Generic API',
	'url' => 'accounts_generic',
	'exchange' => 'generic',
);

require("_accounts_standard.php");

?>

<div class="instructions_add">
<h2>Adding a generic API</h2>

<ol class="steps">
	<li>Copy and paste any URL, starting with <code>http://</code> or <code>https://</code>, into the URL field above and select a currency. Click "Add account".</li>

	<li>The API url needs to return back a single number representing the current value of the given currency.
		<a href="http://code.google.com/p/openclerk/source/browse/trunk/example/generic_api.php" class="php">See an example script.</a></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a generic API URL?</h2>

<ul>
	<li><?php echo htmlspecialchars(get_site_config('site_name')); ?> does not perform any verification
		on the return value; as long as it is a valid number, it will be accepted as a balance.</li>

	<li>Your API URLs will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>The ability to retract or remove generic API access depends entirely on your access to the generic API itself.</li>
</ul>
</div>

<?php


page_footer();
