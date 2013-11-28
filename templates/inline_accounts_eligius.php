<?php
$account_data = array('exchange_name' => get_exchange_name('eligius'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Enter the Bitcoin address that you use as your <?php echo $account_data['exchange_name']; ?> username
		into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_pools')); ?>">"Add new Mining Pool" form</a>, and click "Add account".</li>

	<li>If you would like to also track payments to that address, add this same Bitcoin address to
		<a href="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses#wizard_btc')); ?>">Your Addresses</a>.</li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> your <?php echo $account_data['exchange_name']; ?> BTC address?</h2>

<ul>
	<li>At the time of writing, your <?php echo $account_data['exchange_name']; ?> BTC address can only be used to retrieve account balances and worker status;
		it should not be possible to perform transactions or change user details using your BTC address.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> BTC addresses will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>At any time, you may change the BTC address associated with your <?php echo $account_data['exchange_name']; ?> account.</li>
</ul>
</div>