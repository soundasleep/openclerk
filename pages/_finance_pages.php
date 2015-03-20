
<ul class="page_list">
  <?php
  $args = array();
  if (require_get("demo", false)) {
    $args['demo'] = require_get("demo");
  } ?>
  <li class="page_tabtransactions<?php if (isset($your_transactions) && $your_transactions) echo " page_current"; ?>"><a href="<?php echo htmlspecialchars(url_for('your_transactions', $args)); ?>">
    <?php echo ht("Your Transactions"); ?>
  </a></li>

  <?php
  $args = array();
  if (require_get("demo", false)) {
    $args['demo'] = require_get("demo");
  } ?>
  <li class="page_tabfinanceaccounts<?php if (isset($page_finance_accounts) && $page_finance_accounts) echo " page_current"; ?>"><a href="<?php echo htmlspecialchars(url_for('finance_accounts', $args)); ?>">
    <?php echo ht("Accounts"); ?> <span class="new"><?php echo ht("new"); ?></span>
  </a></li>

  <?php
  $args = array();
  if (require_get("demo", false)) {
    $args['demo'] = require_get("demo");
  } ?>
  <li class="page_tabfinancecategories<?php if (isset($page_finance_categories) && $page_finance_categories) echo " page_current"; ?>"><a href="<?php echo htmlspecialchars(url_for('finance_categories', $args)); ?>">
    <?php echo ht("Categories"); ?> <span class="new"><?php echo ht("new"); ?></span>
  </a></li>

</ul>
