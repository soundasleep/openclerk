<?php
$account_data = array('exchange_name' => get_exchange_name('litecoinglobal'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <a href="https://www.litecoinglobal.com/account"><?php echo $account_data['exchange_name']; ?> account</a> and visit your <i>Account</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/litecoinglobal1.png')); ?>"></li>

	<li>In the <i>Account Information</i> tab, copy and paste the <i>Read-Only API Key</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_securities')); ?>">"Add new Securities Exchange" form</a>, and click "Add account".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/litecoinglobal2.png')); ?>"></li>
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

	<li>At the time of writing, it is not possible to change or reset your <?php echo $account_data['exchange_name']; ?> Read-Only API key.</li>
</ul>
</div>