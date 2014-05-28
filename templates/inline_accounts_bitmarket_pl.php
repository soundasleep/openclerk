<?php
$account_data = array('exchange_name' => get_exchange_name('bitmarket_pl'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <a href="https://www.bitmarket.pl/account.php"><?php echo $account_data['exchange_name']; ?> account ("Konto")</a> and visit the
		<a href="https://www.bitmarket.pl/apikeys.php"><i>API Keys</i> section</a> (<i>Dostęp API</i>).<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitmarket_pl1.png')); ?>"></li>

	<li>Click on the "Generate new API key" button (<i>"Wygeneruj nowy klucz API"</i>).<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitmarket_pl2.png')); ?>"></li>

	<li>Make sure that <i>only</i> the "account balances" (<i>"Pobranie informacji o koncie"</i>) permission is selected,
		and click "generate new key" (<i>"wygeneruj nowy klucz"</i>).<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitmarket_pl3.png')); ?>"></li>

	<li>Copy and paste the <i>API Key</i> (<i>"Klucz jawny"</i>) and <i>API Secret</i> (<i>"Klucz tajny"</i>) into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>, and click "Add account".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitmarket_pl4.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li>You need to make sure that the API key <em>only</em> has the "account balances" (<i>"Pobranie informacji o koncie"</i>) permission. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can revoke an API key&apos;s access at any time by
		going to your <a href="https://www.bitmarket.pl/apikeys.php">API Keys section</a> and clicking on the <i>Cancel key</i> (<i>"Odwołaj klucz"</i>) button.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitmarket_pl_delete.png')); ?>"></li>
</ul>
</div>
