<?php
$account_data = array('exchange_name' => get_exchange_name('coinbase'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Click the "Add Account" button on the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/coinbase1.png')); ?>"></li>

	<li>Authorize <?php echo get_site_config('site_name'); ?> to access your <?php echo $account_data['exchange_name']; ?> account.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/coinbase2.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to authorise <?php echo htmlspecialchars(get_site_config('site_name')); ?> to access your <?php echo $account_data['exchange_name']; ?> account?</h2>

<ul>
	<li><?php echo get_site_config('site_name'); ?> only requests the permission to <i>view your balance</i>, so it is not possible to
		perform transactions or change user details with this permission.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can revoke access for an application to your account at any time,
		by going to your <i>Third Party Applications</i> section of your <a href="https://coinbase.com/account/api" target="_blank">Account Settings</a>
		and clicking on the <i>Revoke Access</i> link.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/coinbase_delete.png')); ?>"></li>
</ul>
</div>