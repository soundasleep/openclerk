<?php
$account_data = array('exchange_name' => get_exchange_name('bitstamp'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <a href="https://www.bitstamp.net/account/balance/"><?php echo $account_data['exchange_name']; ?> account</a>
		and copy your <i>Customer ID</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitstamp0.png')); ?>"></li>

	<li>Click on the Security menu.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitstamp1.png')); ?>"></li>

	<li>Under Security, click on API Access.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitstamp2.png')); ?>"></li>

	<li>Generate a new API Key by selecting <em>only</em> the <i>Account balance</i> permission, and clicking "Generate Key".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitstamp3.png')); ?>"></li>

	<li>You must activate the API key; once copying your <i>Key</i> and <i>Secret</i> to the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>,
		click on "Activate" to activate the key.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitstamp4.png')); ?>"></li>

	<li>You will receive a confirmation e-mail from <?php echo $account_data['exchange_name']; ?>; click on this link to complete the key activation.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitstamp5.png')); ?>"></li>

	<li>Your API key will now be activated. Finally, click "Add Account" on the completed "Add new Exchange" form.</li>

</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li>You need to make sure that the API key <em>only</em> has the <i>Account balance</i> permission. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can deactivate or delete an API key at any time by clicking <i>Deactivate</i> or <i>Delete</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitstamp_delete.png')); ?>"></li>

</ul>
</div>