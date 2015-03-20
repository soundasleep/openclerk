<?php

function get_knowledge_base_title($q) {
  $kb = get_knowledge_base();
  foreach (get_knowledge_base() as $label => $group) {
    foreach ($group as $key => $data) {
      if ($key == $q) {
        return is_array($data) ? $data['title'] : $data;
      }
    }
  }
  return "(Unknown kb article '" . htmlspecialchars($q) . "')";
}

function get_knowledge_base() {
  $kb = array(
    t('Concepts') => array(
      'cryptocurrencies' => array('title' => t("What are cryptocurrencies?"), 'inline' => 'inline_cryptocurrencies'),
      'versions' => array('title' => t(":site_name Version History"), 'inline' => 'inline_versions', 'new' => true),
    ),
    t('Interface') => array(
      'bitcoin_csv' => t("How do I upload a Bitcoin-Qt CSV file?"),
      'litecoin_csv' => t("How do I upload a Litecoin-Qt CSV file?"),
      'notifications' => array('title' => t("How do automated notifications work?"), 'inline' => 'inline_notifications'),
      'managed_graphs' => array('title' => t("How are graphs automatically managed?"), 'inline' => 'inline_managed_graphs'),
      'graph_refresh' => array('title' => t("Do graphs live update?"), 'inline' => 'inline_graph_refresh'),
      'finance' => array('title' => t("What is :site_name Finance?"), 'inline' => 'inline_finance', 'new' => true),
    ),
    t('Accounts') => array(
      'add_currency' => array('title' => t("Can you add support for another cryptocurrency?"), 'inline' => 'inline_add_currency'),
      'add_fiat' => array('title' => t("Can you add support for another fiat currency?"), 'inline' => 'inline_add_fiat'),
      'add_service' => array('title' => t("Can you add support for another exchange/mining pool?"), 'inline' => 'inline_add_service'),
    ),
    t('Notifications') => array(
      'notifications_ticker' => array('title' => t("How do I get notified of exchange rate changes?"), 'inline' => 'inline_notifications_ticker'),
      'notifications_reports' => array('title' => t("How do I get notified of changes in my reports?"), 'inline' => 'inline_notifications_reports'),
      'notifications_hashrates' => array('title' => t("How do I get notified of changes in my hashrates?"), 'inline' => 'inline_notifications_hashrates'),
    ),
    t('Finance') => array(
      'transaction_creation' => array('title' => t("How are transactions automatically created?"), 'inline' => 'inline_transaction_creation', 'new' => true),
    ),
  );

  // automatically construct KB for adding accounts through the wizards
  $wizards = array(
    // group label => kb account title
    "Mining pools" => 'mining pool account',
    "Exchanges" => 'exchange account',
    "Securities" => 'securities exchange account',
    "Individual Securities" => 'securities',
    "Other" => '',
  );
  foreach (account_data_grouped() as $label => $group) {
    if (isset($wizards[$label])) {
      foreach ($group as $key => $data) {
        if ($data['disabled']) {
          continue;
        }
        if ($data['unsafe'] && !get_site_config('allow_unsafe')) {
          // don't display help pages for unsafe accounts
          continue;
        }
        if ($label == 'Individual Securities') {
          $title = 'How do I add individual ' . get_exchange_name($data['exchange']) . (isset($data['suffix']) ? $data['suffix'] : '') . ($wizards[$label] ? ' ' . $wizards[$label] : '') . '?';
        } else {
          $title = 'How do I add a ' . get_exchange_name($key) . (isset($data['suffix']) ? $data['suffix'] : '') . ($wizards[$label] ? ' ' . $wizards[$label] : '') . '?';
        }
        $kb[t('Accounts')][$key] = array(
          'title' => $title,
          'inline' => 'inline_accounts_' . $key,
          'new' => in_array($key, get_new_supported_wallets()) || in_array($key, get_new_exchanges()) || in_array($key, get_new_security_exchanges()) ||
            (isset($data['exchange']) && in_array($data['exchange'], get_new_security_exchanges())),
        );
      }
    }
  }

  // sort each section by title
  foreach ($kb as $label => $group) {
    uasort($kb[$label], '_sort_get_knowledge_base');
  }

  return $kb;
}

function _sort_get_knowledge_base($a, $b) {
  return strcmp(strtolower(isset($a['title']) ? $a['title'] : $a), strtolower(isset($b['title']) ? $b['title'] : $b));
}
