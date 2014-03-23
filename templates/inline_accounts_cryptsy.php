<?php
$account_data = array('exchange_name' => get_exchange_name('cryptsy'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <a href="https://www.cryptsy.com/users/settings"><?php echo $account_data['exchange_name']; ?> account</a> and visit your <i>Settings</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/cryptsy1.png')); ?>"></li>

	<li>Under <i>API Keys</i>, make sure that your API is <i>enabled</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/cryptsy2.png')); ?>"></li>

	<li>Under <i>Application Keys</i>, click on "Add New Application Key".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/cryptsy3.png')); ?>"></li>

	<li>Select an <i>App ID</i> (for example, "Openclerk").
		Make sure that the key has no permissions enabled, fill out any 2FA fields necessary, and click "Add Application Key".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/cryptsy4.png')); ?>"></li>

	<li>Refresh the page. Once the page has reloaded, click on the <i>Edit Key</i> button for your new application key to view the necessary keys.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/cryptsy5.png')); ?>"></li>

	<li>Copy and paste your <i>Application Key</i> and <i>App ID</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>, and click "Add account".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/cryptsy6.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> application API key?</h2>

<ul>
	<li>The <?php echo $account_data['exchange_name']; ?> application API key that you provide is not given any permissions, except to retrieve account status.
		This means that it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can revoke a set of API keys&apos; access at any time by
		going to <i>Application Keys</i> and clicking on the <i>Remove Application Key</i> button.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/cryptsy-delete.png')); ?>"></li>
</ul>
</div>