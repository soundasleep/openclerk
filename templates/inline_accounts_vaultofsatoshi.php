<?php
$account_data = array('exchange_name' => get_exchange_name('vaultofsatoshi'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <?php echo $account_data['exchange_name']; ?> account
		and visit your <a href="https://www.vaultofsatoshi.com/myapikeys">API Keys Administration</a> page.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/vaultofsatoshi1.png')); ?>"></li>

	<li>Click on the "New Key" button to generate a new API key.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/vaultofsatoshi2.png')); ?>"></li>

	<li>Select <em>only</em> the <i>Get Info</i> permission for this new API key, and save your changes.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/vaultofsatoshi3.png')); ?>"></li>

	<li>A dialog box will pop up with the <i>API secret key</i> for that API key; this will only be displayed once.
		Copy this API secret key into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/vaultofsatoshi4.png')); ?>"></li>

	<li>Finally, the <i>API key</i> will be displayed; copy this API key into the "Add new Exchange" form, and finally click "Add Account".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/vaultofsatoshi5.png')); ?>"></li>

</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li>You need to make sure that the API key <em>only</em> has the <i>Get Info</i> permission. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can deactivate or delete an API key at any time by clicking the <i>Revoke</i> button
		through your <a href="https://www.vaultofsatoshi.com/myapikeys">API Keys Administration</a> page.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/vaultofsatoshi_delete.png')); ?>"></li>

</ul>
</div>