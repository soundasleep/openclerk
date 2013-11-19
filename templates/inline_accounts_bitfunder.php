<?php
$account_data = array('exchange_name' => get_exchange_name('bitfunder'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <?php echo $account_data['exchange_name']; ?> account
		and visit your <a href="https://bitfunder.com/settings">Settings page</a>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitfunder1.png')); ?>"></li>

	<li>Copy and paste your <i>Public Bitcoin Address</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_securities')); ?>">"Add new Securities Exchange" form</a>, and click "Add account".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitfunder2.png')); ?>"></li>

	<li><?php echo htmlspecialchars(get_site_config('site_name')); ?> will match your Public Bitcoin Address against
		the BitFunder <a href="https://bitfunder.com/assetlist" target="_blank">Public Asset Holdings List</a> to calculate the value of
		securities that you own. Currently there is no API access for accessing your wallet balance.</li>

	<li>If you have forgotten your Public Bitcoin Address, you will need to generate a new one, since BitFunder will
		not display your current address.</li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> your <?php echo $account_data['exchange_name']; ?> Public Bitcoin Address?</h2>

<ul>
	<li>A public Bitcoin address can only be used to retrieve the balance of that address, and to match
		against the Public Asset Holdings List; it is not possible to perform transactions with a public Bitcoin address.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> Public Bitcoin Addresses will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can change your Public Bitcoin Address at any time.</li>
</ul>
</div>