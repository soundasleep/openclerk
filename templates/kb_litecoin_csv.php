<?php
global $title;
?>

<h1><?php echo $title; ?></h1>

<p><a href="<?php echo htmlspecialchars(url_for("help")); ?>"><?php echo ht("< Back to Help"); ?></a></p>

<div class="kb_text">

<div class="instructions_add">
<h2><?php echo ht("Uploading a Litecoin-Qt CSV file"); ?></h2>

<p>
<?php echo ht("If you are using the default Litecoin-Qt client, you can
use the 'export' feature of the client to automatically populate your list of LTC addresses using your existing address labels.
Any invalid or duplicated addresses will be skipped."); ?>
</p>

<ol class="steps">
	<li><?php echo t('Open your Litecoin-Qt client, and
		open the "Receive coins" tab.'); ?><br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/litecoinqt1.png')); ?>"></li>

	<li><?php echo t('Click the "Export" button and save this CSV file to your computer.'); ?><br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/litecoinqt2.png')); ?>"></li>

	<li><?php echo t('Once this CSV file has
		been exported, select the "Browse..." button on the "Upload CSV" tab
		on the :page
		to locate and upload this file to :site_name.',
		array(':page' => link_to(url_for('wizard_accounts_addresses#wizard_ltc'), ht("add LTC Addresses page")))); ?><br>
		<img src="<?php echo htmlspecialchars(url_for('img/accounts/litecoinqt3.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2><?php echo t("Is it safe to provide :site_name a Litecoin-Qt CSV file?"); ?></h2>

<ul>
	<li><?php echo t("The Litecoin-Qt client will only export your public
		Litecoin addresses. These addresses can only be used to retrieve
		address balances; it is not possible to perform transactions using a public address."); ?></li>
</ul>
</div>

</div>
