<div class="instructions_add">
<h2>Adding a Pool-x.eu account</h2>

<ol class="steps">
	<li>Log into your <a href="http://pool-x.eu/accountdetails">Pool-x.eu account details</a>.</li>

	<li>Find your <i>API Key</i>, as illustrated below: <br>
	<img src="img/accounts/poolx.png"></li>

	<li>Copy and paste this <i>API Key</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_pools')); ?>">"Add new Mining Pool" form</a>, and click "Add account".</li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a Pool-x.eu API key?</h2>

<ul>
	<li>At the time of writing, a Pool-x.eu API key can only be used to retrieve account balances and
		worker status; it is not possible to perform transactions or change user details using the API key.</li>

	<li>Your Pool-x.eu API keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>At the time of writing, it is not possible to change or reset your Pool-x.eu API key.</li>
</ul>
</div>
