<?php
$account_data = array('exchange_name' => get_exchange_name('cryptsy'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<h3 class="unsafe_warning">Warning: Providing your <?php echo $account_data['exchange_name']; ?> account data is <a href="http://code.google.com/p/openclerk/wiki/Unsafe">unsafe</a>.</h3>

<ol class="steps">
	<li>Log into your <a href="https://mtgox.com/security"><?php echo $account_data['exchange_name']; ?> account</a> and visit your <i>Settings</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/cryptsy1.png')); ?>"></li>

	<li>Under <i>API Keys</i>, make sure that your API is <i>enabled</i>.
		Copy and paste your <i>Public Key</i> and <i>Private Key</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>, and click "Add account".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/cryptsy2.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li><strong class="unsafe_warning">Providing <?php echo $account_data['exchange_name']; ?> account data is <a href="http://code.google.com/p/openclerk/wiki/Unsafe">unsafe</a>;</strong>
		you need to ensure that you trust this server, and that it is not possible for somebody to access your host or database.</li>

	<li><?php echo $account_data['exchange_name']; ?> API keys are <strong>not read-only</strong>, and if someone was to
		access your account data, they could make trades or withdraw funds using these keys.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can revoke a set of API keys&apos; access at any time by
		going to <i>API Keys</i> and clicking on the green <i>Generate New Key</i> icon.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/cryptsy3.png')); ?>"></li>
</ul>
</div>