<?php
$account_data = array('exchange_name' => get_exchange_name('bips'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <?php echo $account_data['exchange_name']; ?> account
		and visit your <a href="https://bips.me/merchant">Merchant page</a>.<br>
		<img src="img/accounts/bips1.png"></li>

	<li>Browse down to <i>API Keys</i>, select the <i>GetBalance</i> tab, and click
		on <i>Get new GetBalance API key</i>.<br>
		<img src="img/accounts/bips2.png"></li>

	<li>Browse back down to <i>API Keys</i>, and select the <i>GetBalance</i> tab again. Copy and
		paste this new <i>API Key</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>, and click "Add account".<br>
		<img src="img/accounts/bips3.png"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li>At the time of writing, a <?php echo $account_data['exchange_name']; ?> GetBalance API key can only be used to retrieve account balances;
		it should not be possible to perform transactions or change user details using a GetBalance API key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> API keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can revoke an API key&apos;s access at any time by
		going to the <i>GetBalance API Keys</i> section of the Merchant page, and clicking on the blue <i>Delete</i> icon.</li>
</ul>
</div>