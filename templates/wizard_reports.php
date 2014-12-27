<?php
global $user;
?>
<div class="wizard-steps">
  <h2><?php echo t("Preferences Wizard"); ?></h2>
  <ul>
    <li class="past"><a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>"><?php echo t("Currencies"); ?></a></li>
    <li class="past"><a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>"><?php echo t("Accounts"); ?></a></li>
    <li class="current"><a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>"><?php echo t("Reports"); ?></a></li>
    <li class=""><a href="<?php echo htmlspecialchars(url_for('profile')); ?>"><?php echo t("Your Reports"); ?></a></li>
  </ul>
</div>

<div class="wizard-content">
<h1><?php echo t("Report Preferences"); ?></h1>

<p>
  <?php echo t('Once information from your accounts have been downloaded and compiled into reports,
  you may view :reports by defining graphs and pages.
  :site_name can automatically manage these reports for you, or you may opt to define these manually.
  (You can always change these accounts later, by selecting the "Configure Accounts" link above.)',
    array(
      ':reports' => link_to(url_for('profile'), t('these reports')),
    ));
  ?>
</p>

<!--<p class="tip tip_float your_account_limits">-->
<p>
<?php
echo ht("As a :user, you may have up to :graphs per page defined.",
  array(
    ':user' => $user['is_premium'] ? ht("premium user") : ht("free user"),
    ':graphs' => plural("graph", get_premium_value($user, 'graphs_per_page')),
  ));
echo "\n";
if (!$user['is_premium']) {
  echo t("To increase this limit, please purchase a :premium_account.", array(':premium_account' => link_to(url_for('premium'), ht("premium account"))));
}
?>
</p>

<p><a href="<?php echo htmlspecialchars(url_for('help/managed_graphs')); ?>"><?php echo htmlspecialchars(get_knowledge_base_title('managed_graphs')); ?></a></p>
