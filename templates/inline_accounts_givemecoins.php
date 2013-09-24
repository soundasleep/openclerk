<?php
$account_data = array('exchange_name' => get_exchange_name('givemecoins'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
	<li>Log into your <a href="https://give-me-coins.com/pool/accountdetails"><?php echo $account_data['exchange_name']; ?> Account Details</a>.<br>
		<img src="img/accounts/givemecoins2.png"></li>

	<li>Find your <i>API Key</i>, as illustrated below: <br>
		<img src="img/accounts/givemecoins3.png"></li>

	<li>Copy and paste this <i>API Key</i> into the "Add new" form, and click "Add account".</li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
	<li>At the time of writing, a <?php echo $account_data['exchange_name']; ?> API key can only be used to retrieve account balances and
		worker status; it is not possible to perform transactions or change user details using the API key.</li>

	<li>Your <?php echo $account_data['exchange_name']; ?> API keys will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>At the time of writing, it is not possible to change or reset your <?php echo $account_data['exchange_name']; ?> API key.</li>
</ul>
</div>