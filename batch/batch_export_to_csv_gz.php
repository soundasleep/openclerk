<?php

require(__DIR__ . "/../inc/global.php");

db()->getPDO()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

$q = db()->prepare("SELECT * FROM users");
$q->execute(array());
$users = $q->fetchAll();

function escape_csv($s) {
  return "\"" . str_replace("\"", "\"\"", $s) . "\"";
}

function exportData($table, $user, $q) {
  $fp = null;
  $header = array();
  $headerCsv = array();

  $i = 0;
  while ($row = $q->fetch()) {
    $i++;
    if ($i % 10000 == 0) {
      echo "$i...\n";
      flush();
    }

    if ($fp == null) {
      if (!file_exists("${user['id']}/")) {
        mkdir("${user['id']}", 0777, true);
      }
      $filename = "${user['id']}/${table}.csv";
      echo "Writing $filename...\n";
      flush();
      $fp = fopen($filename, "w") or die("Could not open $filename");

      // write headers
      $keys = array_keys($row);
      foreach ($keys as $key) {
        if (is_numeric($key)) {
          continue;
        }
        $header[] = $key;
        $headerCsv[] = escape_csv($key);
      }

      fwrite($fp, join($headerCsv, ",") . "\n");
    }

    $exportedCsv = array();
    foreach ($header as $key) {
      $exportedCsv[] = escape_csv($row[$key]);
    }
    fwrite($fp, join($exportedCsv, ",") . "\n");
  }

  if ($fp != null) {
    fclose($fp);
  }
}

function exportUserIdTable($table, $user, $user_id_key = "user_id") {
  $q = db()->prepare("SELECT * FROM $table WHERE $user_id_key=?") or die("Could not export $table for $user_id_key");
  $q->execute(array($user['id']));

  exportData($table, $user, $q);
}

foreach ($users as $user) {
  exportUserIdTable("users", $user, "id");
  exportUserIdTable("user_oauth2_identities", $user, "id");
  exportUserIdTable("user_openid_identities", $user, "id");
  exportUserIdTable("user_passwords", $user, "id");
  exportUserIdTable("user_properties", $user, "id");
  exportUserIdTable("user_valid_keys", $user, "id");

  $account_tables = array(
    "accounts_796", "accounts_anxpro", "accounts_bit2c", "accounts_bitfunder", "accounts_bitmarket_pl", "accounts_bitminter",
    "accounts_bitnz", "accounts_bitstamp", "accounts_bittrex", "accounts_btce", "accounts_btcguild", "accounts_btcinve",
    "accounts_btclevels", "accounts_btct", "accounts_cexio", "accounts_coinbase", "accounts_cryptopools_dgc", "accounts_cryptostocks",
    "accounts_cryptotroll_doge", "accounts_cryptsy", "accounts_d2_wdc", "accounts_ecoining_ppc", "accounts_eligius", "accounts_eobot",
    "accounts_generic", "accounts_ghashio", "accounts_givemecoins", "accounts_hashfaster_doge", "accounts_hashfaster_ltc",
    "accounts_hashtocoins", "accounts_havelock", "accounts_individual_796", "accounts_individual_bitfunder", "accounts_individual_btcinve",
    "accounts_individual_btct", "accounts_individual_cryptostocks", "accounts_individual_cryptotrade", "accounts_individual_havelock",
    "accounts_individual_litecoinglobal", "accounts_individual_litecoininvest", "accounts_justcoin_anx", "accounts_khore",
    "accounts_kraken", "accounts_litecoinglobal", "accounts_litecoininvest", "accounts_litecoinpool", "accounts_liteguardian",
    "accounts_multipool", "accounts_nicehash", "accounts_ozcoin_btc", "accounts_ozcoin_ltc", "accounts_poloniex", "accounts_slush",
    "accounts_triplemining", "accounts_vircurex", "accounts_wemineltc", "accounts_westhash", "accounts_ypool",

    "addresses", "address_balances", "balances", "finance_accounts", "finance_categories",
    "graph_data_balances", "graph_data_summary", "graph_pages", "hashrates",
    "managed_graphs", "notifications", "offsets", "outstanding_premiums", "pending_subscriptions",

    "securities", "securities_796", "securities_bitfunder", "securities_btcinve",
    "securities_btct", "securities_cryptostocks", "securities_cryptotrade",
    "securities_havelock", "securities_litecoinglobal", "securities_litecoininvest",

    "summaries", "summary_instances", "vote_coins_votes",
  );

  foreach ($account_tables as $table) {
    exportUserIdTable($table, $user);
  }

  $q = db()->prepare("SELECT * FROM graphs WHERE page_id IN (select id FROM graph_pages WHERE user_id=?)");
  $q->execute(array($user['id']));

  exportData('graphs', $user, $q);

  $q = db()->prepare("SELECT * FROM graph_technicals WHERE graph_id IN (SELECT id FROM graphs WHERE page_id IN (select id FROM graph_pages WHERE user_id=?))");
  $q->execute(array($user['id']));

  exportData('graph_technicals', $user, $q);

  // notifications_address_balances, notifications_balances, notifications_hashrates are all empty tables
  // (were never implemented by openclerk)

  $q = db()->prepare("SELECT * FROM notifications_summary_instances WHERE id IN (SELECT type_id FROM notifications WHERE user_id=? AND notification_type='summary_instance')");
  $q->execute(array($user['id']));

  exportData('notifications_summary_instances', $user, $q);

  $q = db()->prepare("SELECT * FROM notifications_ticker WHERE id IN (SELECT type_id FROM notifications WHERE user_id=? AND notification_type='ticker')");
  $q->execute(array($user['id']));

  exportData('notifications_ticker', $user, $q);

  // `tar -zcvf ${user['id']}.tar.gz ${user['id']}`;
  echo "Compressing...\n";
  flush();
  `cd ${user['id']} && zip -r ${user['id']}.zip * && cd ..`;
}
