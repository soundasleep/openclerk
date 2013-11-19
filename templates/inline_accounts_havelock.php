<?php
$account_data = array('exchange_name' => get_exchange_name('havelock'));
?>

<div class="instructions_add">
<h2>Adding a Havelock Investments account</h2>

<ol class="steps">
	<li>Log into your <a href="https://www.havelockinvestments.com/api.php">Havelock Investments account</a> and visit your <i>API Setup</i>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/havelock1.png')); ?>"></li>

	<li>Create a new API key by entering in a <i>Key Name</i> and clicking "Create Key".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/havelock2.png')); ?>"></li>

	<li>For this new key, select the <i>Portfolio</i> and <i>Balance</i> permissions, and click "Save Key Permissions".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/havelock3.png')); ?>"></li>

	<li>Copy and paste this long <i>API Key</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_securities')); ?>">"Add new Securities Exchange" form</a>, and click "Add account".<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/havelock4.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a Havelock Investments API key?</h2>

<ul>
	<li>The API key that you provide is only given the Balance and Portfolio permissions. This should
		mean that the API key can only be used to retrieve account status, and it should not be possible
		to perform trades or withdraw funds using that key.</li>

	<li>Your Havelock Investments keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>You may remove an API key at any time by visiting your <i>API Setup</i> page and clicking the "Remove" button
		for the key, which will	revoke any existing access.</li>
</ul>
</div>
