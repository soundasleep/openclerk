<?php
$account_data = array('exchange_name' => get_exchange_name('vircurex'));
?>

<div class="instructions_add">
<h2>Adding a Vircurex account</h2>

<ol class="steps">
	<li>Log into your <a href="https://vircurex.com/accounts">Vircurex account</a> and visit <i>Settings</i> in the top right navigation.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/vircurex1.png')); ?>"></li>

	<li>Under the <i>API tab</i>, check <i>Get balance</i>, insert in any random secret string - for example,
		you can use <a href="https://www.grc.com/passwords.htm">GRC&apos;s random password generator</a> to generate
		a 63-character random alphanumeric string - and click <i>Save</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/vircurex2.png')); ?>"></li>

	<li>Copy and paste your both your username and your chosen random secret string into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>, and click "Add account".</li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> your Vircurex API data?</h2>

<ul>
	<li>You need to make sure that the API key <em>only</em> has the <i>Get balance</i> permission. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your Vircurex username and secret will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in. Your secrets will also not be displayed on the Vircurex site.</li>

	<li>Through the Vircurex interface you can revoke an API key&apos;s access at any time by
		going to the <i>API</i> tab and either unchecking <i>Get balance</i>, or replacing the secret.</li>
</ul>
</div>
