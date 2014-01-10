<?php
$account_data = array('exchange_name' => get_exchange_name('bitcurex_pln'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <a href="https://pln.bitcurex.com/klucze-api"><?php echo $account_data['exchange_name']; ?> account</a> and visit the <i>API Trading</i> section (<i>API transakcyjne</i>).<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitcurex_pln0.png')); ?>"></li>

	<li>Click on "generate new keys" (<i>"wygeneruj nowe klucze"</i>).<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitcurex_pln1.png')); ?>"></li>

	<li>Select "prohibited" (<i>"zabroniona"</i>) for <strong>all of the methods except <i>getFunds</i></strong>, and click "generate API key" (<i>"wygeneruj klucze API"</i>).
		Make sure that the <i>getFunds</i> method is allowed (<i>"dozwolona"</i>).<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitcurex_pln2.png')); ?>"></li>

	<li>Copy and paste the <i>API Key</i> and <i>Secret</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>, and click "Add account".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitcurex_pln3.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li>You need to make sure that the API key <em>only</em> has the <i>getFunds</i> permission. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can revoke an API key&apos;s access at any time by
		going to <i>Current API Keys</i> and clicking on the <i>Delete Key</i> (<i>"skasuj ten klucz"</i>) button.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitcurex_pln_delete.png')); ?>"></li>
</ul>
</div>