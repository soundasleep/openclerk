<?php
$account_data = array('exchange_name' => get_exchange_name('mtgox'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <a href="https://mtgox.com/security"><?php echo $account_data['exchange_name']; ?> account</a> and visit the <i>security center</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/mtgox1.png')); ?>"></li>

	<li>Under <i>Advanced API Key Creation</i>, create a name for a new key (such as "<?php echo htmlspecialchars(get_site_config('site_name')); ?>");
		give the key <i>Get Info</i> rights; and click <i>Create</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/mtgox2.png')); ?>"></li>

	<li>Copy and paste the <i>API Key</i> and <i>Secret</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>, and click "Add account". Click "Update" on the Mt.Gox site to save your changes.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/mtgox3.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li>You need to make sure that the API key <em>only</em> has the <i>Get Info</i> permission. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>Through the <?php echo $account_data['exchange_name']; ?> interface you can revoke an API key&apos;s access at any time by
		going to <i>Current API Keys</i> and clicking on the red <i>Delete</i> icon.</li>
</ul>
</div>