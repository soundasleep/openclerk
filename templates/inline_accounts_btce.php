<?php
$account_data = array('exchange_name' => get_exchange_name('btce'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <a href="https://btc-e.com/profile"><?php echo $account_data['exchange_name']; ?> profile</a>.<br>
	<img src="img/accounts/btce1.png"></li>

	<li>From the profile, select <i>API keys</i>.<br>
	<img src="img/accounts/btce2.png"></li>

	<li>Create a name for a new key (such as "<?php echo htmlspecialchars(get_site_config('site_name')); ?>") and click <i>Create</i>.<br>
	<img src="img/accounts/btce3.png"></li>

	<li>Select the <i>info</i> permission, and click <i>Save</i>. Once the key has been saved, copy and paste the <i>Key</i> and <i>Secret</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>, and click "Add account".<br>
	<img src="img/accounts/btce4.png"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li>You need to make sure that the API key <em>only</em> has the <i>info</i> permission. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can revoke an API key&apos;s access at any time by clicking <i>Disable</i>.</li>
</ul>
</div>