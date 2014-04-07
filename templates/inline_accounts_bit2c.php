<?php
$account_data = array('exchange_name' => get_exchange_name('bit2c'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into <a href="https://www.bit2c.co.il/account/edit">your <?php echo $account_data['exchange_name']; ?> account</a> and visit the API tab.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bit2c0.png')); ?>"></li>

	<li>Click on "Create new".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bit2c1.png')); ?>"></li>

	<li>Enter any title you want, make sure "Readonly" is selected and click "Confirm".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bit2c2.png')); ?>"></li>

	<li>Copy and paste your <i>Key</i> and <i>Secret</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>, and click "Add account".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bit2c3.png')); ?>"></li>

</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li>You need to make sure that the API key is marked as <i>Readonly</i>. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can revoke a set of API keys&apos; access at any time by
		going to your <a href="https://www.bit2c.co.il/FixAPI/index">API</a> page and clicking the <i>delete</i> button after below the API key.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bit2c_delete1.png')); ?>"></li>

	<li>Alternatively, you can disable access by clicking the <i>edit</i> button, unselecting the "<i>Is Active</i>" checkbox and clicking <i>Save</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bit2c_delete2.png')); ?>"></li>
</ul>
</div>