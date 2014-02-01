<?php
$account_data = array('exchange_name' => get_exchange_name('btcinve'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <a href="https://www.btcinve.com/account/api"><?php echo $account_data['exchange_name']; ?> account</a> and go to the <i>Private API</i> section of your <i>Account Settings</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/btcinve1.png')); ?>"></li>

	<li>Click on the <i>Generate private API key</i> button to generate a new API key.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/btcinve2.png')); ?>"></li>

	<li>Copy and paste this new <i>API Key</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_securities')); ?>">"Add new Securities Exchange" form</a>, and click "Add account".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/btcinve3.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> Read-Only API key?</h2>

<ul>
	<li>The API key that you provide is a read-only API key. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can revoke an API key&apos;s access at any time, by
		revisiting your <a href="https://www.btcinve.com/account/api">Private API page</a> and clicking on the <i>Generate new private API key</i> or <i>Revoke private API key</i> buttons.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/btcinve_delete.png')); ?>"></li>

</ul>
</div>