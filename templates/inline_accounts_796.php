<?php
$account_data = array('exchange_name' => get_exchange_name('796'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <a href="https://www.bitstamp.net/account/balance/"><?php echo $account_data['exchange_name']; ?> User Panel</a>
		and visit <i>Account Security</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/7961.png')); ?>"></li>

	<li>Click on the <i>Trade API</i> tab.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/7962.png')); ?>"></li>

	<li>Click on <i>Creating API key</i> to create a new API key.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/7963.png')); ?>"></li>

	<li>Enter in a new name for this API key, and click <i>Submit</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/7964.png')); ?>"></li>

	<li>Make sure that none of the trading permissions are set on this new API key.
		Copy and paste the <i>Application ID</i>, <i>Key</i> and <i>Secret</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_securities')); ?>">"Add new Securities Exchange" form</a>, and click "Add account".
		Click <i>Submit</i> on the API key details.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/7965.png')); ?>"></li>

</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li>You need to make sure that the API key <em>does not </em> have any trading permissions. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can delete an API key at any time by clicking <i>Delete</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/796_delete.png')); ?>"></li>

</ul>
</div>