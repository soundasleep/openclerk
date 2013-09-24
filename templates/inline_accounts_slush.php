<?php
$account_data = array('exchange_name' => get_exchange_name('slush'));
?>

<div class="instructions_add">
<h2>Adding a Slush&apos;s pool account</h2>

<ol class="steps">
	<li>Log into your <a href="https://mining.bitcoin.cz/accounts/profile/">Slush&apos;s pool account details</a>, and select
		the <i>Manage API tokens</i> tab.<br>
		<img src="img/accounts/slush1.png"></li>

	<li>Find your <i>current token</i>, as illustrated below: <br>
		<img src="img/accounts/slush2.png"></li>

	<li>Copy and paste this <i>current token</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_pools')); ?>">"Add new Mining Pool" form</a>, and click "Add account".</li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a Slush&apos;s pool API key?</h2>

<ul>
	<li>At the time of writing, a Slush&apos;s pool API key can only be used to retrieve account balances and
		worker status; it is not possible to perform transactions or change user details using the API key.</li>

	<li>Your Slush&apos;s pool API keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>You may generate a new API token at any time by visiting your <i>Manage API Tokens</i> page, which will
		revoke any existing access.</li>
</ul>
</div>