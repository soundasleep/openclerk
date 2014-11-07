<?php
$account_data = array('exchange_name' => get_exchange_name('btclevels'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <a href="https://btclevels.com/security"><?php echo $account_data['exchange_name']; ?> account</a>
		and visit your API Keys.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/btclevels1.png')); ?>"></li>

	<li>Enter in a new key name, select only the <i>Info</i> permission for this key, and click "Add".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/btclevels2.png')); ?>"></li>

	<li>Copy and paste the <i>API Key</i> and <i>API Secret</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>, and click "Add account".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/btclevels3.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li>You need to make sure that the API key <em>does not</em> have the <i>Trade</i> permission. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can remove an API key at any time by clicking <i>Remove</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/btclevels_delete.png')); ?>"></li>

</ul>
</div>
