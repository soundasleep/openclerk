<?php

require(__DIR__ . "/../inc/global.php");

db()->getPDO()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

$q = db()->prepare("SELECT * FROM users");
$q->execute(array());
$users = $q->fetchAll();

function escape_csv($s) {
  return "\"" . str_replace("\"", "\"\"", $s) . "\"";
}

function format_number($n) {
  if (is_null($n)) {
    return null;
  } else {
    return $n * 1;
  }
}

function format_time($t) {
  if (is_null($t)) {
    return null;
  } else {
    return "$t +0000";
  }
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

$filename = "all-users.csv";
echo "Writing $filename...\n";
flush();
$fp = fopen($filename, "w") or die("Could not open $filename");

foreach ($users as $user) {
  fwrite($fp, $user['email'] . "," . $user['id'] . "\n");
}

fclose($fp);

foreach ($users as $user) {
  exportUserIdTable("users", $user, "id");
  exportUserIdTable("user_oauth2_identities", $user);
  exportUserIdTable("user_openid_identities", $user);
  exportUserIdTable("user_passwords", $user);
  exportUserIdTable("user_properties", $user, "id");
  exportUserIdTable("user_valid_keys", $user);

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

  // export a JSON file suitable for cryptfolio.com import
  // this file includes user details, charts, graphs, notifications,
  // and accounts - and their latest balances -
  // but not old historical data. cryptfolio.com does not support
  // uploading balances data yet (only txns), but this can be done manually by
  // users in the future.
  $json = array(
    'id'          => format_number($user['id']),
    'exported_at' => date('c'),
  );
  $json['user'] = array(
    'id'         => format_number($user['id']),
    'created_at' => format_time($user['created_at']),
    'email'      => $user['email'],
    'last_login' => format_time($user['last_login']),
  );

  $q = db()->prepare("SELECT * FROM user_properties WHERE id=?");
  $q->execute(array($user['id']));
  $properties = $q->fetch() or die("No user properties for user ${user['id']}");

  $to_copy = array('name', 'country', 'user_ip',
        'referer', 'graph_managed_type', 'preferred_crypto', 'preferred_fiat',
        'locale');
  sort($to_copy);
  foreach ($to_copy as $key) {
    $json['user'][$key] = $properties[$key];
  }

  $to_copy_boolean = array('is_admin', 'is_system', 'is_premium', 'is_disabled', 'is_deleted', 'subscribe_announcements', 'is_deleted', 'has_added_account');
  sort($to_copy_boolean);
  foreach ($to_copy_boolean as $key) {
    $json['user'][$key] = $properties[$key] == '1';
  }

  $to_copy_numeric = array('notifications_sent', 'emails_sent');
  sort($to_copy_numeric);
  foreach ($to_copy_numeric as $key) {
    $json['user'][$key] = format_number($properties[$key]);
  }

  $to_copy_time = array('premium_expires', 'disabled_at', 'disable_warned_at', 'updated_at', 'last_queue', 'last_report_queue', 'first_report_sent', 'last_summaries_update', 'last_account_change', 'last_sum_job', 'requested_delete_at');
  sort($to_copy_time);
  foreach ($to_copy_time as $key) {
    $json['user'][$key] = format_time($properties[$key]);
  }

  $q = db()->prepare("SELECT * FROM user_oauth2_identities WHERE id=?");
  $q->execute(array($user['id']));
  $json['user']['oauth2'] = array();
  while ($row = $q->fetch()) {
    $json['user']['oauth2'][] = array(
      'id'         => format_number($row['id']),
      'provider'   => $row['provider'],
      'created_at' => format_time($row['created_at']),
      'uid'        => $row['uid'],
    );
  }

  $q = db()->prepare("SELECT * FROM user_openid_identities WHERE id=?");
  $q->execute(array($user['id']));
  $json['user']['openid'] = array();
  while ($row = $q->fetch()) {
    $json['user']['openid'][] = array(
      'id'         => format_number($row['id']),
      'created_at' => format_time($row['created_at']),
      'identity'   => $row['identity'],
    );
  }

  // no point in including passwords, they're hashed so we can't reverse them
  $q = db()->prepare("SELECT COUNT(*) AS c FROM user_passwords WHERE id=?");
  $q->execute(array($user['id']));
  $json['user']['passwords'] = $q->fetch()['c'] * 1;

  // for each account table
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
  );

  $json['accounts'] = array();
  foreach ($account_tables as $table) {
    $account_type = substr($table, strlen("accounts_"));

    $q = db()->prepare("SELECT * FROM $table WHERE user_id=?");
    $q->execute(array($user['id']));
    while ($row = $q->fetch()) {
      $to_copy = array(
        'type' => $account_type,
        'balances' => array(),
      );

      foreach ($row as $key => $value) {
        if (is_numeric($key)) continue;
        if ($key == "user_id") continue;
        if ($key == "id" || $key == "failures") {
          $to_copy[$key] = format_number($value);
        } elseif ($key == "is_disabled" || $key == "is_disabled_manually") {
          $to_copy[$key] = $value == '1';
        } elseif ($key == "created_at" || $key == "updated_at" || $key == "last_login" || $key == "premium_expires" || $key == "disabled_at" || $key == "last_queue") {
          $to_copy[$key] = format_time($value);
        } else {
          $to_copy[$key] = $value;
        }
      }

      $json['accounts'][] = $to_copy;
    }
  }

  // get most recent balances
  foreach ($json['accounts'] as $i => $address) {
    $q = db()->prepare("SELECT * FROM balances WHERE account_id=? AND is_recent=1");
    $q->execute(array($address['id']));
    while ($row = $q->fetch()) {
      $json['accounts'][$i]['balances'][] = array(
        'id'         => format_number($row['id']),
        'balance'    => $row['balance'],
        'currency'   => $row['currency'],
        'created_at' => format_time($row['created_at']),
      );
    }
  }

  $json['addresses'] = array();
  $q = db()->prepare("SELECT * FROM addresses WHERE user_id=?");
  $q->execute(array($user['id']));
  while ($row = $q->fetch()) {
    $to_copy = array(
      'id'         => format_number($row['id']),
      'created_at' => format_time($row['created_at']),
      'last_queue' => format_time($row['last_queue']),
      'currency'   => $row['currency'],
      'address'    => $row['address'],
      'title'      => $row['title'],
      'balance'    => null,
    );

    $json['addresses'][] = $to_copy;
  }

  // get most recent balances
  foreach ($json['addresses'] as $i => $address) {
    $q = db()->prepare("SELECT * FROM address_balances WHERE address_id=? AND is_recent=1");
    $q->execute(array($address['id']));
    while ($row = $q->fetch()) {
      $json['addresses'][$i]['balance'] = array(
        'id'         => format_number($row['id']),
        'balance'    => $row['balance'],
        'created_at' => format_time($row['created_at']),
      );
    }
  }

  // offsets
  $json['offsets'] = array();
  $q = db()->prepare("SELECT * FROM offsets WHERE user_id=?");
  $q->execute(array($user['id']));
  while ($row = $q->fetch()) {
    $to_copy = array(
      'id'         => format_number($row['id']),
      'created_at' => format_time($row['created_at']),
      'currency'   => $row['currency'],
      'title'      => $row['title'],
      'balance'    => $row['balance'],
    );

    $json['offsets'][] = $to_copy;
  }

  // graphs
  $json['pages'] = array();
  $q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=?");
  $q->execute(array($user['id']));
  while ($row = $q->fetch()) {
    $json['pages'][] = array(
      'id'         => format_number($row['id']),
      'title'      => $row['title'],
      'created_at' => format_time($row['created_at']),
      'updated_at' => format_time($row['updated_at']),
      'page_order' => format_number($row['page_order']),
      'is_removed' => $row['is_removed'] == '1',
      'is_managed' => $row['is_managed'] == '1',

      'graphs'     => array(),
    );
  }

  foreach ($json['pages'] as $i => $page) {
    $q = db()->prepare("SELECT * FROM graphs WHERE page_id=?");
    $q->execute(array($page['id']));
    while ($row = $q->fetch()) {
      $json['pages'][$i]['graphs'][] = array(
        'id'         => format_number($row['id']),
        'created_at' => format_time($row['created_at']),
        'graph_type' => $row['graph_type'],
        'arg0'       => $row['arg0'],
        'width'      => format_number($row['width']),
        'height'     => format_number($row['height']),
        'page_order' => format_number($row['page_order']),
        'is_removed' => $row['is_removed'] == '1',
        'days'       => format_number($row['days']),
        'string0'    => $row['string0'],
        'is_managed' => $row['is_managed'] == '1',
        'delta'      => $row['delta'],

        'technicals' => array(),
      );
    }

    foreach ($json['pages'][$i]['graphs'] as $j => $graph) {
      $q = db()->prepare("SELECT * FROM graph_technicals WHERE graph_id=?");
      $q->execute(array($graph['id']));
      while ($row = $q->fetch()) {
        $json['pages'][$i]['graphs'][$j]['technicals'][] = array(
          'id'         => $row['id'] * 1,
          'created_at' => format_time($row['created_at']),
          'type'       => $row['technical_type'],
          'period'     => format_number($row['technical_period']),
        );
      }
    }
  }

  // notificationses!
  $json['notifications'] = array();
  $q = db()->prepare("SELECT * FROM notifications WHERE user_id=?");
  $q->execute(array($user['id']));
  while ($row = $q->fetch()) {
    $to_copy = array(
      'id'         => format_number($row['id']),
      'created_at' => format_time($row['created_at']),
      'last_queue' => format_time($row['last_queue']),
      'last_value' => format_number($row['last_value']),
      'type'       => $row['notification_type'],
      'type_id'    => format_number($row['type_id']),

      'trigger_condition' => $row['trigger_condition'],
      'trigger_value' => format_number($row['trigger_value']),
      'period'     => $row['period'],

      'is_percent' => $row['is_percent'] == '1',
      'is_disabled' => $row['is_disabled'] == '1',
      'is_notified' => $row['is_notified'] == '1',

      'last_notification' => format_time($row['last_notification']),
      'notifications_sent' => format_number($row['notifications_sent']),
    );

    $json['notifications'][] = $to_copy;
  }

  foreach ($json['notifications'] as $i => $notification) {
    switch ($notification['type']) {
      case "summary_instance":
        $q = db()->prepare("SELECT * FROM notifications_summary_instances WHERE id=?");
        $q->execute(array($notification['type_id']));
        while ($row = $q->fetch()) {
          $json['notifications'][$i]['summary_type'] = $row['summary_type'];
        }
        break;

      case "ticker":
        $q = db()->prepare("SELECT * FROM notifications_ticker WHERE id=?");
        $q->execute(array($notification['type_id']));
        while ($row = $q->fetch()) {
          $json['notifications'][$i]['exchange'] = $row['exchange'];
          $json['notifications'][$i]['currency1'] = $row['currency1'];
          $json['notifications'][$i]['currency2'] = $row['currency2'];
        }
        break;

      default:
        die("Unknown notification type " . $notification['type']);
    }
  }

  // summaries (these were used to capture which currencies to track)
  $json['summaries'] = array();
  $q = db()->prepare("SELECT * FROM summaries WHERE user_id=?");
  $q->execute(array($user['id']));
  while ($row = $q->fetch()) {
    $to_copy = array(
      'id'         => format_number($row['id']),
      'created_at' => format_time($row['created_at']),
      'type'       => $row['summary_type'],
      'balance'    => null,
    );

    $json['summaries'][] = $to_copy;
  }

  // summary_instances (latest only)
  foreach ($json['summaries'] as $i => $summary) {
    $q = db()->prepare("SELECT * FROM summary_instances WHERE user_id=? AND summary_type=? AND is_recent=1");
    $q->execute(array($user['id'], $summary['type']));
    while ($row = $q->fetch()) {
      $json['summaries'][$i]['balance'] = array(
        'id'         => format_number($row['id']),
        'created_at' => format_time($row['created_at']),
        'balance'    => $row['balance'],
      );
    }
  }

  // outstanding premiums
  $json['premiums'] = array();
  $q = db()->prepare("SELECT * FROM outstanding_premiums WHERE user_id=?");
  $q->execute(array($user['id']));
  while ($row = $q->fetch()) {
    $to_copy = array(
      'id'         => format_number($row['id']),
      'created_at' => format_time($row['created_at']),
      'paid_at'    => format_time($row['paid_at']),
      'address'    => null,
      'address_id' => format_number($row['address_id']),
      'balance'    => $row['balance'],
      'months'     => format_number($row['months']),
      'years'      => format_number($row['years']),
      'last_reminder' => format_time($row['last_reminder']),
      'paid_balance' => $row['paid_balance'],
      'last_balance' => $row['last_balance'],

      'is_paid' => $row['is_paid'] == '1',
      'is_unpaid' => $row['is_unpaid'] == '1',
    );

    $json['premiums'][] = $to_copy;
  }

  foreach ($json['premiums'] as $i => $premium) {
    $q = db()->prepare("SELECT * FROM addresses WHERE id=?");
    $q->execute(array($premium['address_id']));
    while ($row = $q->fetch()) {
      $json['premiums'][$i]['address'] = array(
        'id'         => format_number($row['id']),
        'created_at' => format_time($row['created_at']),
        'last_queue' => format_time($row['last_queue']),
        'currency'   => $row['currency'],
        'address'    => $row['address'],
        'title'      => $row['title'],
      );
    }
  }

  // JSON does not include:
  // finance accounts, finance categories, hashrates,
  // pending_subscriptions (these are for mailing lists),
  // anything securities, vote_coins

  // write JSON
  $filename = "${user['id']}/cryptfolio.json";
  echo "Writing $filename...\n";
  flush();
  file_put_contents($filename, json_encode($json, JSON_PRETTY_PRINT));

  // `tar -zcvf ${user['id']}.tar.gz ${user['id']}`;
  echo "Compressing...\n";
  flush();
  `cd ${user['id']} && zip -r ${user['id']}.zip * && cd ..`;

  // write alternative JSON
  if (!file_exists("output/")) {
    mkdir("output", 0777, true);
  }

  $filename = "output/${user['id']}.json";
  echo "Writing $filename...\n";
  flush();
  file_put_contents($filename, json_encode($json, JSON_PRETTY_PRINT));
}
