<?php
$account_data = array('exchange_name' => get_exchange_name('poloniex'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <a href="https://www.poloniex.com/apiKeys"><?php echo $account_data['exchange_name']; ?> account</a>
		and visit the <i>API Keys</i> page.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/poloniex1.png')); ?>"></li>

	<li>If your account API is not already enabled, click "Enable API" and follow the instructions.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/poloniex2.png')); ?>"></li>

	<li>Click "Create New" to create a new API key.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/poloniex3.png')); ?>"></li>

	<li><b>WARNING:</b> By default <?php echo $account_data['exchange_name']; ?> API keys permit currency trading, but do not permit automatic currency withdrawls.
		You <strong>must confirm</strong> that by providing <?php echo get_site_config('site_name'); ?> with your <?php echo $account_data['exchange_name']; ?> API key,
		you accept that <?php echo get_site_config('site_name'); ?> cannot guarantee the safety and security of this unsafe API key,
		and you will not hold <?php echo get_site_config('site_name'); ?> liable for any damages as a result of providing <?php echo $account_data['exchange_name']; ?> API keys.
		</li>

	<li>Copy and paste the <i>API Key</i> and <i>API Secret</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>, and click "Add account".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/poloniex4.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li><b>WARNING:</b> By default <?php echo $account_data['exchange_name']; ?> API keys permit currency trading,
		and all of the other account methods as described in the <a href="https://www.poloniex.com/api"><?php echo $account_data['exchange_name']; ?> API documentation</a>,
		but do not permit automatic currency withdrawls.
		To add <?php echo $account_data['exchange_name']; ?> API keys to <?php echo get_site_config('site_name'); ?>, you must
		accept the risk that the safety and security of your <?php echo $account_data['exchange_name']; ?> API keys cannot be guaranteed.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <a href="https://www.poloniex.com/apiKeys"><?php echo $account_data['exchange_name']; ?> interface</a> you can delete an API key at any time by selecting <i>Delete</i> on your API key and clicking <i>Update</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/poloniex_delete.png')); ?>"></li>

</ul>
</div>
