<?php

/**
 * This page is the second page in a series of wizards to configure a user account.
 * A user may revisit this page at any time to reconfigure their account.
 * This page allows the user to select which kind of accounts to add.
 */

require("inc/global.php");
require_login();

require("layout/templates.php");
page_header("Add Accounts and Addresses", "page_wizard_accounts", array('jquery' => true, 'js' => 'wizard'));

$user = get_user(user_id());
require_user($user);

$messages = array();

// get all of our accounts
$accounts = user_limits_summary(user_id());

require_template("wizard_accounts");

?>

<div class="wizard">

<ul class="account-type">

	<li><a href="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses')); ?>">Address
		<?php if ($accounts['wizard_addresses']) { ?><span class="count">(<?php echo number_format($accounts['wizard_addresses']); ?>)<?php } ?></a>
		<ul>
			<li>e.g. Bitcoin addresses, Litecoin addresses</li>
		</ul>
	</li>

	<li><a href="<?php echo htmlspecialchars(url_for('wizard_accounts_pools')); ?>">Mining Pool
		<?php if ($accounts['wizard_pools']) { ?><span class="count">(<?php echo number_format($accounts['wizard_pools']); ?>)<?php } ?></a>
		<ul>
			<li>e.g. Slush&apos;s pool, Give Me Coins, BTC Guild</li>
		</ul>
	</li>

	<li><a href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchange')); ?>">Exchange
		<?php if ($accounts['wizard_exchanges']) { ?><span class="count">(<?php echo number_format($accounts['wizard_exchanges']); ?>)<?php } ?></a>
		<ul>
			<li>e.g. Mt.Gox, Bitstamp, BitNZ, VirtEx</li>
		</ul>
	</li>

	<li><a href="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses')); ?>">Securities
		<?php if ($accounts['wizard_securities']) { ?><span class="count">(<?php echo number_format($accounts['wizard_securities']); ?>)<?php } ?></a>
		<ul>
			<li>e.g. BTC-TC, Litecoin Global, Cryptostocks</li>
		</ul>
	</li>

	<li><a href="<?php echo htmlspecialchars(url_for('wizard_accounts_other')); ?>">Other
		<?php if ($accounts['wizard_other']) { ?><span class="count">(<?php echo number_format($accounts['wizard_other']); ?>)<?php } ?></a>
		<ul>
			<li>e.g. Generic APIs</li>
		</ul>
	</li>

</ul>

<div class="wizard-buttons">
<a class="button" href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>">&lt; Previous</a>
<a class="button submit" href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>">Next &gt;</a>
</div>
</div>

<?php

require_template("wizard_accounts_footer");

page_footer();
