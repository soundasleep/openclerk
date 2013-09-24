<?php
$account_data = array('exchange_name' => get_exchange_name('cryptostocks'));
?>

<div class="instructions_add">
<h2>Adding a Cryptostocks account</h2>

<ol class="steps">
	<li>Log into your <a href="https://cryptostocks.com/settings/edit">Cryptostocks account</a> and visit your <i>Account</i>.<br>
		<img src="img/accounts/cryptostocks1.png"></li>

	<li>In the <i>API Secret Words</i> section, enter in two different random secret strings - for example,
		you can use <a href="https://www.grc.com/passwords.htm">GRC&apos;s random password generator</a> to generate
		a 63-character random alphanumeric string - into both the <i>get_coin_balances</i> and <i>get_share_balances</i> fields. Click <i>Save</i>.<br>
		<img src="img/accounts/cryptostocks2.png">

	<li>Copy and paste these keys, along with your Cryptostocks account e-mail address, into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_securities')); ?>">"Add new Securities Exchange" form</a> and click "Add account".</li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> your Cryptostocks API keys and e-mail address?</h2>

<ul>
	<li>You need to make sure that the two secret words are different, and are not used in any of the other
		secret word fields. This should mean that the secret words can only be used to retrieve account status,
		and it should not be possible to perform trades or withdraw funds using that key.</li>

	<li>Your Cryptostocks e-mail address and keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the Cryptostocks interface you can revoke any key&apos;s access at any time by
		going to <i>API Secret Words</i> and either changing, or removing, the secret word.</li>
</ul>
</div>