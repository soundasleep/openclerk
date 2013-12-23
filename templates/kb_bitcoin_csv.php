<?php
global $title;
?>

<h1><?php echo $title; ?></h1>

<p><a href="<?php echo htmlspecialchars(url_for("help")); ?>">&lt; Back to Help</a></p>

<div class="kb_text">

<div class="instructions_add">
<h2>Uploading a Bitcoin-Qt CSV file</h2>

<p>
If you are using the default Bitcoin-Qt client, you can
use the "export" feature of the client to automatically populate your list of BTC addresses using your existing address labels.
Any invalid or duplicated addresses will be skipped.
</p>

<ol class="steps">
	<li>Open your Bitcoin-Qt client, and
		open the "Receive coins" tab.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitcoinqt1.png')); ?>"></li>

	<li>Click the "Export" button and save this CSV file to your computer.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitcoinqt2.png')); ?>"></li>

	<li>Once this CSV file has
		been exported, select the "Browse..." button on the "Upload CSV" tab
		on the <a href="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses#wizard_btc')); ?>">add BTC Addresses page</a>
		to locate and upload this file to <?php echo htmlspecialchars(get_site_config('site_name')); ?>.<br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitcoinqt3.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a Bitcoin-Qt CSV file?</h2>

<ul>
	<li>The Bitcoin-Qt client will only export your <i>public</i>
		Bitcoin addresses. These addresses can only be used to retrieve
		address balances; it is not possible to perform transactions using a public address.</li>
</ul>
</div>

</div>