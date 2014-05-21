<?php
global $title;
?>

<h1><?php echo $title; ?></h1>

<p><a href="<?php echo htmlspecialchars(url_for("help")); ?>"><?php echo ht("< Back to Help"); ?></a></p>

<div class="kb_text">

<div class="instructions_add">
<h2><?php echo ht("Uploading a Bitcoin-Qt CSV file"); ?></h2>

<p>
<?php echo ht('If you are using the default Bitcoin-Qt client, you can
use the "export" feature of the client to automatically populate your list of BTC addresses using your existing address labels.
Any invalid or duplicated addresses will be skipped.'); ?>
</p>

<ol class="steps">
	<li><?php echo ht('Open your Bitcoin-Qt client, and
		open the "Receive coins" tab.'); ?><br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitcoinqt1.png')); ?>"></li>

	<li><?php echo ht('Click the "Export" button and save this CSV file to your computer.'); ?><br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitcoinqt2.png')); ?>"></li>

	<li><?php echo t('Once this CSV file has
		been exported, select the "Browse..." button on the "Upload CSV" tab
		on the :page to locate and upload this file to :site_name.',
		array(':page' => link_to(url_for('wizard_accounts_addresses#wizard_btc'), ht("add BTC Addresses page")))); ?><br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/bitcoinqt3.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2><?php echo ht("Is it safe to provide :site_name a Bitcoin-Qt CSV file?"); ?></h2>

<ul>
	<li><?php echo t("The Bitcoin-Qt client will only export your public
		Bitcoin addresses. These addresses can only be used to retrieve
		address balances; it is not possible to perform transactions using a public address."); ?></li>
</ul>
</div>

</div>
