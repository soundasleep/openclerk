<?php
$account_data = array('exchange_name' => get_exchange_name('bitminter'));
?>

<div class="instructions_add">
<h2>Adding a BitMinter account</h2>

<ol class="steps">
	<li>Log into your <a href="https://bitminter.com/login">BitMinter account</a>, and select
		the <i>Donations &amp; perks</i> link under "My Account".<br>
		<img src="img/accounts/bitminter1.png"></li>

	<li>Adjust your BTC and NMC donation amounts so that you can enable the "API" key.<br>
		<img src="img/accounts/bitminter23.png"></li>

	<li>Select the <i>API keys</i> link under "My Account".<br>
		<img src="img/accounts/bitminter4.png"></li>

	<li>Create a new API key by entering in a label, and clicking "Add".<br>
		<img src="img/accounts/bitminter5.png"></li>

	<li>Copy and paste this new <i>Key</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_pools')); ?>">"Add new Mining Pool" form</a>, and click "Add account".<br>
		<img src="img/accounts/bitminter6.png"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a BitMinter API key?</h2>

<ul>
	<li>At the time of writing, a BitMinter API key can only be used to retrieve account balances and
		worker status; it is not possible to perform transactions or change user details using the API key.</li>

	<li>Your BitMinter API keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>You may revoke access to existing API keys at any time by visiting your <i>API keys</i> page and removing the key.</li>
</ul>
</div>