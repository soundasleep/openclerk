<?php

/**
 * Callback for Ajax to update wizard table for manual testing.
 */

require(__DIR__ . "/../layout/templates.php");

$user = get_user(user_id());
require_user($user);

$exchange = require_get('exchange');
$id = require_get('id');

// make sure that we actually have a valid account
$account_data = false;
$accounts = array();
foreach (account_data_grouped() as $label => $data) {
  foreach ($data as $key => $value) {
    if ($key == $exchange) {
      // we've found a valid account type
      $account_data = get_accounts_wizard_config($key);
      $account_type = get_wizard_account_type($value['wizard']);
      $add_types[] = $key;
      $add_type_names[$key] = get_exchange_name($key) . (isset($value['suffix']) ? $value['suffix'] : "");

      $q = db()->prepare("SELECT * FROM " . $account_data['table'] . "
        WHERE user_id=? AND id=? ORDER BY title ASC");
      $q->execute(array(user_id(), $id));
      while ($r = $q->fetch()) {
        $r['exchange'] = $key;
        $r['khash'] = $account_data['khash'];
        $accounts[] = $r;
      }
    }
  }
}

if (!$account_data) {
  throw new Exception("No account data found for exchange '" . htmlspecialchars($exchange) . "'");
}

// no header or footer; we just output straight HTML

// uses $accounts to generate rows;
// this is in an include so we can also use it in wizard_accounts_callback
$is_in_callback = true; // don't display the <tr>s
require(__DIR__ . "/_wizard_accounts_rows.php");

?>
