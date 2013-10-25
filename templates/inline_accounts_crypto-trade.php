<?php
$account_data = array('exchange_name' => get_exchange_name('crypto-trade'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <?php echo $account_data['exchange_name']; ?> account and visit your <a href="https://crypto-trade.com/member/apisettings">API Settings</a>.<br>
	<img src="img/accounts/crypto-trade1.png"></li>

	<li>Select the <i>Info</i> permission, and click the "Generate New API" button.<br>
	<img src="img/accounts/crypto-trade2.png"></li>

	<li>Copy and paste the <i>API Key</i> and <i>API Secret</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>, and click "Add account".<br>
	<img src="img/accounts/crypto-trade3.png"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li>You need to make sure that the API key <em>only</em> has the <i>Info</i> permission. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can revoke an API key&apos;s access at any time by generating a new API key/secret.<br>
		<img src="img/accounts/crypto-trade_delete.png"></li>
</ul>
</div>