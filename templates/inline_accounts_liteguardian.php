<?php
$account_data = array('exchange_name' => get_exchange_name('liteguardian'));
?>

<div class="instructions_add">
<h2>Adding a LiteGuardian account</h2>

<ol class="steps">
	<li>Log into your <a href="https://www.liteguardian.com/secure/user/manageAccount">LiteGuardian account details</a>.<br>
		<img src="img/accounts/liteguardian1.png"></li>

	<li>Find your <i>Api Key</i>, as illustrated below: <br>
		<img src="img/accounts/liteguardian2.png"></li>

	<li>Copy and paste this <i>Api Key</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_pools')); ?>">"Add new Mining Pool" form</a>, and click "Add account".</li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a LiteGuardian API key?</h2>

<ul>
	<li>At the time of writing, a LiteGuardian API key can only be used to retrieve account balances and
		worker status; it is not possible to perform transactions or change user details using the API key.</li>

	<li>Your LiteGuardian API keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>At the time of writing, it is not possible to change or reset your LiteGuardian API key.</li>
</ul>
</div>