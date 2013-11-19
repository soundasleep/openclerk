<?php
global $title;
?>

<h1><?php echo $title; ?></h1>

<p><a href="<?php echo htmlspecialchars(url_for("help")); ?>">&lt; Back to Help</a></p>

<div class="kb_text">

<div class="instructions_add">
<h2>Uploading a Litecoin-Qt CSV file</h2>

<ol class="steps">
	<li>Open your Litecoin-Qt client, and
		open the "Receive coins" tab.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/litecoinqt1.png')); ?>"></li>

	<li>Click the "Export" button and save this CSV file to your computer.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/litecoinqt2.png')); ?>"></li>

	<li>Once this CSV file has
		been exported, select the "Browse..." button on the "Upload CSV" tab
		on the <a href="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses#wizard_ltc')); ?>">add LTC Addresses page</a>
		to locate and upload this file to <?php echo htmlspecialchars(get_site_config('site_name')); ?>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/litecoinqt3.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a Litecoin-Qt CSV file?</h2>

<ul>
	<li>The Litecoin-Qt client will only export your <i>public</i>
		Litecoin addresses. These addresses can only be used to retrieve
		address balances; it is not possible to perform transactions using a public address.</li>
</ul>
</div>

</div>