<?php
$account_data = array('exchange_name' => get_exchange_name('bittrex'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <?php echo $account_data['exchange_name']; ?> account and visit your
		<a href="https://bittrex.com/Account/ManageApiKey">API Keys</a>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bittrex0.png')); ?>"></li>

	<li>Click on the "Add New Key" button.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bittrex1.png')); ?>"></li>

	<li>Select <strong>only</strong> the <i>Read Info</i> permission, and click "Save Changes".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bittrex2.png')); ?>"></li>

	<li>Copy and paste the <i>Key</i> and <i>Secret</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>, and click "Add account".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bittrex3.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li>You need to make sure that the API key <em>only</em> has the <i>Read Info</i> permission. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can revoke an API key&apos;s access at any time by
		going to your <a href="https://bittrex.com/Account/ManageApiKey">API Keys</a> and clicking on the <i>Delete</i> button.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bittrex_delete.png')); ?>"></li>
</ul>
</div>
