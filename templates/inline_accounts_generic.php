<?php
$account_data = array('exchange_name' => get_exchange_name('generic'));
?>

<div class="instructions_add">
<h2>Adding a generic API</h2>

<ol class="steps">
	<li>Copy and paste any URL, starting with <code>http://</code> or <code>https://</code>, into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Other Account" form</a> and select a currency. Click "Add account".</li>

	<li>The API url needs to return back a single number representing the current value of the given currency.
		<a href="http://code.google.com/p/openclerk/source/browse/trunk/example/generic_api.php" class="php">See an example script.</a></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a generic API URL?</h2>

<ul>
	<li><?php echo htmlspecialchars(get_site_config('site_name')); ?> does not perform any verification
		on the return value; as long as it is a valid number, it will be accepted as a balance.</li>

	<li>Your API URLs will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		site, even if you have logged in.</li>

	<li>The ability to retract or remove generic API access depends entirely on your access to the generic API itself.</li>
</ul>
</div>
