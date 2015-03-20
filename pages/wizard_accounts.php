<?php

/**
 * This page is the second page in a series of wizards to configure a user account.
 * A user may revisit this page at any time to reconfigure their account.
 * This page allows the user to select which kind of accounts to add.
 */

require_login();

require(__DIR__ . "/../layout/templates.php");
page_header(t("Add Accounts and Addresses"), "page_wizard_accounts", array('js' => 'wizard', 'class' => 'page_accounts'));

$user = get_user(user_id());
require_user($user);

$messages = array();

// get all of our accounts
$accounts = user_limits_summary(user_id());

// get our offset values
require(__DIR__ . "/../graphs/util.php");
$summaries = get_all_summary_currencies();
$currencies = get_all_currencies();

require_template("wizard_accounts");

?>

<div class="wizard">

<ul class="account-type">

  <li><a href="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses')); ?>"><?php echo t("Address"); ?>
    <?php if ($accounts['wizard_addresses']) { ?><span class="count">(<?php echo number_format($accounts['wizard_addresses']); ?>)<?php } ?></a>
    <ul>
      <li><?php echo t("e.g."); ?> Bitcoin <?php echo t("addresses"); ?>, Litecoin <?php echo t("addresses"); ?></li>
    </ul>
  </li>

  <li><a href="<?php echo htmlspecialchars(url_for('wizard_accounts_pools')); ?>"><?php echo t("Mining Pool"); ?>
    <?php if ($accounts['wizard_pools']) { ?><span class="count">(<?php echo number_format($accounts['wizard_pools']); ?>)<?php } ?></a>
    <ul>
      <li><?php echo t("e.g."); ?> Slush&apos;s pool, Give Me Coins, BTC Guild</li>
    </ul>
  </li>

  <li><a href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>"><?php echo t("Exchange"); ?>
    <?php if ($accounts['wizard_exchanges']) { ?><span class="count">(<?php echo number_format($accounts['wizard_exchanges']); ?>)<?php } ?></a>
    <ul>
      <li><?php echo t("e.g."); ?> Coinbase, BTC-e, Bitstamp, Vircurex</li>
    </ul>
  </li>

  <li><a href="<?php echo htmlspecialchars(url_for('wizard_accounts_securities')); ?>"><?php echo t("Securities"); ?>
    <?php if ($accounts['wizard_securities']) { ?><span class="count">(<?php echo number_format($accounts['wizard_securities']); ?>)<?php } ?></a>
    <ul>
      <li><?php echo t("e.g."); ?> Havelock Investments, Cryptostocks</li>
    </ul>
  </li>

</ul>

<ul class="account-type">

  <li><a href="<?php echo htmlspecialchars(url_for('wizard_accounts_offsets')); ?>"><?php echo t("Offsets"); ?>
    <?php if ($accounts['wizard_offsets']) { ?><span class="count">(<?php echo number_format($accounts['wizard_offsets']); ?>)<?php } ?></a>
    <ul>
      <li><?php echo t("e.g."); ?> <?php echo ht("Additional balances"); ?> <span class="new"><?php echo ht("new"); ?></span></li>
    </ul>
  </li>

  <li><a href="<?php echo htmlspecialchars(url_for('wizard_accounts_other')); ?>"><?php echo t("Other"); ?>
    <?php if ($accounts['wizard_other']) { ?><span class="count">(<?php echo number_format($accounts['wizard_other']); ?>)<?php } ?></a>
    <ul>
      <li><?php echo t("e.g."); ?> Generic APIs</li>
    </ul>
  </li>

  <li></li>

  <li><a href="<?php echo htmlspecialchars(url_for('wizard_notifications')); ?>"><?php echo t("Notifications"); ?>
    <?php if ($accounts['wizard_notifications']) { ?><span class="count">(<?php echo number_format($accounts['wizard_notifications']); ?>)<?php } ?></a>
    <ul>
      <li><?php echo t("e.g."); ?> <?php echo t("Hashrates"); ?>, <?php echo t("exchange rates"); ?></li>
    </ul>
  </li>

</ul>

<div style="clear:both;"></div>

<div class="wizard-buttons">
<a class="button" href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>"><?php echo ht("< Previous"); ?></a>
<a class="button submit" href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>"><?php echo ht("Next >"); ?></a>
</div>
</div>

<?php

require_template("wizard_accounts_footer");

page_footer();
