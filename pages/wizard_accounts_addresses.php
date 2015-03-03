<?php

/**
 * This page is the second page in a series of wizards to configure a user account.
 * A user may revisit this page at any time to reconfigure their account.
 * This page allows the user to select which kind of accounts to add.
 */

require_login();

define('USE_MASTER_DB', true);    // always use the master database for selects!

require(__DIR__ . "/../graphs/util.php");

require(__DIR__ . "/../layout/templates.php");
page_header(t("Add Addresses"), "page_wizard_accounts_addresses", array('js' => array('accounts', 'wizard'), 'class' => 'page_accounts wizard_page'));

global $user;
$user = get_user(user_id());
require_user($user);

$messages = array();

// get all of our currencies
$summaries = get_all_summaries();
$currencies = array();
foreach ($summaries as $key => $summary) {
  $c = substr($key, strlen("summary_"), 3);
  if (in_array($c, get_all_cryptocurrencies())) {
    $currencies[] = $c;
  }
}
// order by the order defined in crypto.php, and only ones that we can actually address
$currencies = array_intersect(get_address_currencies(), $currencies);

require_template("wizard_accounts_addresses");

?>

<div class="wizard">

<div class="tabs" id="tabs_wizard">
  <ul class="tab_list">
    <?php /* each <li> must not have any whitespace between them otherwise whitespace will appear when rendered */ ?>
    <?php foreach ($currencies as $c) {
      echo "<li id=\"tab_wizard_" . $c . "\"><span class=\"currency_name_" . $c . "\">" . htmlspecialchars(get_currency_name($c)) . "</span></li>";
    } ?>
  </ul>

  <ul class="tab_groups">
  <?php $first_tab = true;
  foreach ($currencies as $c) { ?>
  <li id="tab_wizard_<?php echo $c; ?>_tab"<?php echo $first_tab ? "" : " style=\"display:none;\""; ?>>
    <?php
      $account_data = get_blockchain_wizard_config($c);
      require(__DIR__ . "/_wizard_addresses.php");
    ?>
  </li>
  <?php $first_tab = false;
  } ?>
  </ul>
</div>

<div class="wizard-buttons">
<a class="button" href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>"><?php echo ht("< Previous"); ?></a>
</div>
</div>

<?php

require_template("wizard_accounts_addresses_footer");

page_footer();
