<?php

/**
 * Defines all of the interesting properties of the web appliation:
 * what currencies are supported, what pairs, etc.
 */

use \Openclerk\Currencies\Currency;
use \DiscoveredComponents\Currencies;
use \DiscoveredComponents\Exchanges;
use \DiscoveredComponents\Accounts;
use \DiscoveredComponents\SecurityExchanges;

/**
 * Allow us to define our own sort order for currency lists.
 * Sorts by a defined order, and then alphabetically for any undefined currency codes.
 */
function sort_currency_list($a, $b) {
  $desired = array(
    "btc", "ltc", "nmc", "ppc", "ftc", "xpm", "nvc", "trc", "dog", "mec", "xrp", "dgc", "wdc", "ixc", "vtc", "net", "bc1", "hbn", "drk", "vrc", "nxt", "rdd", "via", "nbt", "nsr",
    "usd", "gbp", "eur", "cad", "aud", "nzd", "cny", "pln", "ils", "krw", "sgd", "dkk", "inr",
    "ghs",
  );
  $as = array_search($a, $desired);
  $bs = array_search($b, $desired);
  if ($as === false) {
    if ($bs === false) {
      return strcmp($a, $b);
    } else {
      return 1;
    }
  }
  if ($bs === false) {
    return -1;
  }
  if ($as == $bs) {
    return 0;
  }
  return $as < $bs ? -1 : 1;
}

/**
 * Return order does not matter.
 * @return a list of currencies which can be hashed
 */
function get_all_hashrate_currencies() {
  return Currencies::getHashableCurrencies();
}

$_cached_get_all_currencies = null;
function get_all_currencies() {
  global $_cached_get_all_currencies;
  if ($_cached_get_all_currencies === null) {
    $currencies = Currencies::getKeys();
    usort($currencies, 'sort_currency_list');
    $_cached_get_all_currencies = $currencies;
  }
  return $_cached_get_all_currencies;
}

/**
 * @return true if this currency is a SHA256 currency and measured in MH/s rather than KH/s
 */
function is_hashrate_mhash($cur) {
  if (in_array($cur, Currencies::getHashableCurrencies())) {
    $instance = Currencies::getInstance($cur);
    $algorithm = $instance->getAlgorithm();
    $algorithm_instance = Algorithms::getInstance($algorithm);
    return $algorithm_instance->getDivisor() >= 1e6;
  }

  return false;
}

// TODO we should be able to get this from the database somehow
function get_new_supported_currencies() {
  return array("sj1");
}

$_cached_get_all_cryptocurrencies = null;
function get_all_cryptocurrencies() {
  global $_cached_get_all_cryptocurrencies;
  if ($_cached_get_all_cryptocurrencies === null) {
    $currencies = Currencies::getCryptocurrencies();
    usort($currencies, 'sort_currency_list');
    $_cached_get_all_cryptocurrencies = $currencies;
  }
  return $_cached_get_all_cryptocurrencies;
}

$_cached_get_all_commodity_currencies = null;
function get_all_commodity_currencies() {
  global $_cached_get_all_commodity_currencies;
  if ($_cached_get_all_commodity_currencies === null) {
    $currencies = Currencies::getCommodityCurrencies();
    usort($currencies, 'sort_currency_list');
    $_cached_get_all_commodity_currencies = $currencies;
  }
  return $_cached_get_all_commodity_currencies;
}

$_cached_get_all_fiat_currencies = null;
function get_all_fiat_currencies() {
  global $_cached_get_all_fiat_currencies;
  if ($_cached_get_all_fiat_currencies === null) {
    $currencies = Currencies::getFiatCurrencies();
    usort($currencies, 'sort_currency_list');
    $_cached_get_all_fiat_currencies = $currencies;
  }
  return $_cached_get_all_fiat_currencies;
}

function is_fiat_currency($cur) {
  return in_array($cur, get_all_fiat_currencies());
}

$_cached_get_address_currencies = null;
/**
 * Currencies which we can download balances using explorers etc
 */
function get_address_currencies() {
  global $_cached_get_address_currencies;
  if ($_cached_get_address_currencies === null) {
    $currencies = Currencies::getAddressCurrencies();
    usort($currencies, 'sort_currency_list');
    $_cached_get_address_currencies = $currencies;
  }
  return $_cached_get_address_currencies;
}

function get_currency_name($cur) {
  if (in_array($cur, Currencies::getKeys())) {
    $currency = Currencies::getInstance($cur);
    return $currency->getName();
  }

  return "Unknown (" . htmlspecialchars($cur) . ")";
}

function get_currency_abbr($c) {
  if (in_array($c, Currencies::getKeys())) {
    $currency = Currencies::getInstance($c);
    return $currency->getAbbr();
  }

  return strtoupper($c);
}

/**
 * Reverse of {@link get_currency_abbr()}.
 */
function get_currency_key($c) {
  if (in_array($c, Currencies::getAbbrs())) {
    return Currencies::getKeyForAbbr($c);
  }

  return strtolower($c);
}

function get_blockchain_currencies() {
  $explorers = array();
  foreach (Currencies::getBalanceCurrencies() as $key) {
    $currency = Currencies::getInstance($key);
    $explorer = $currency->getExplorerName();
    if (!isset($explorers[$explorer])) {
      $explorers[$explorer] = array();
    }
    $explorers[$explorer][] = $key;
  }

  return $explorers;
}

$_get_all_exchanges = null;
function get_all_exchanges() {
  global $_get_all_exchanges;
  if ($_get_all_exchanges === null) {

    $exchanges = array(
      "litecoinglobal" =>  "Litecoin Global",
      "litecoinglobal_wallet" => "Litecoin Global (Wallet)",
      "litecoinglobal_securities" => "Litecoin Global (Securities)",
      "cryptostocks" =>   "Cryptostocks",
      "cryptostocks_wallet" => "Cryptostocks (Wallet)",
      "cryptostocks_securities" => "Cryptostocks (Securities)",
      "bitfunder"     => "BitFunder",
      "bitfunder_wallet"  => "BitFunder (Wallet)",
      "bitfunder_securities" => "BitFunder (Securities)",
      "individual_litecoinglobal" => "Litecoin Global (Individual Securities)",
      "individual_bitfunder" => "BitFunder (Individual Securities)",
      "individual_cryptostocks" => "Cryptostocks (Individual Securities)",
      "individual_crypto-trade" => "Crypto-Trade (Individual Securities)",
      "individual_796" => "796 Xchange (Individual Securities)",
      "generic" =>    "Generic API",
      "offsets" =>    "Offsets",    // generic
      "blockchain" =>   "Blockchain", // generic
      "crypto-trade_securities" => "Crypto-Trade (Securities)",
      "796" =>      "796 Xchange",
      "796_wallet" =>   "796 Xchange (Wallet)",
      "796_securities" => "796 Xchange (Securities)",
      "litecoininvest" => "Litecoininvest",
      "litecoininvest_wallet" => "Litecoininvest (Wallet)",
      "litecoininvest_securities" => "Litecoininvest (Securities)",
      "individual_litecoininvest" => "Litecoininvest (Individual Securities)",
      "btcinve" => "BTCInve",
      "btcinve_wallet" => "BTCInve (Wallet)",
      "btcinve_securities" => "BTCInve (Securities)",
      "individual_btcinve" => "BTCInve (Individual Securities)",
      "average" => "Market Average",
      "ripple" => "Ripple",   // other ledger balances in Ripple accounts are stored as account balances

      // for failing server jobs
      "securities_havelock" => "Havelock Investments security",
      "securities_796" => "796 Xchange security",
      "securities_litecoininvest" => "Litecoininvest security",
    );

    // add discovered exchanges
    foreach (Exchanges::getAllInstances() as $key => $exchange) {
      $exchanges[$key] = $exchange->getName();
    }

    // add discovered accounts
    foreach (Accounts::getAllInstances() as $key => $account) {
      $exchanges[$key] = $account->getName();
    }

    // add discovered accounts
    foreach (SecurityExchanges::getAllInstances() as $key => $account) {
      $exchanges[$key] = $account->getName();
      $exchanges[$key . "_wallet"] = $account->getName() . " (" . t("Wallet") . ")";
      $exchanges[$key . "_securities"] = $account->getName() . " (" . t("Securities") . ")";
      $exchanges["individual_" . $key . "_wallet"] = $account->getName() . " (" . t("Individual Securities") . ")";
    }

    $_get_all_exchanges = $exchanges;
  }

  return $_get_all_exchanges;
}

function get_exchange_name($n) {
  if (in_array($n, Exchanges::getKeys())) {
    $exchange = Exchanges::getInstance($n);
    return $exchange->getName();
  }

  if (in_array($n, Accounts::getKeys())) {
    $account = Accounts::getInstance($n);
    return $account->getName();
  }

  $exchanges = get_all_exchanges();
  if (isset($exchanges[$n])) {
    return $exchanges[$n];
  }

  return "Unknown (" . htmlspecialchars($n) . "]";
}

// these are just new exchange pairs; not new exchange wallets
function get_new_exchanges() {
  return array("bittrex", "bter");
}

/**
 * Get all exchange codes and their currently supported pairs.
 * Does not return the exchange codes in alphabetical order.
 * Does not return any exchange pairs for disabled exchanges.
 */
function get_exchange_pairs() {
  $pairs = array();

  // add all discovered pairs
  foreach (Exchanges::getAllInstances() as $key => $exchange) {
    // ignore all disabled exchanges
    if (in_array($key, Exchanges::getDisabled())) {
      continue;
    }

    $persistent = new \Core\PersistentExchange($exchange, db());
    $result = array();
    foreach ($persistent->getMarkets() as $pair) {
      if (in_array($pair[0], get_all_currencies()) && in_array($pair[1], get_all_currencies())) {
        $result[] = $pair;
      }
    }

    $pairs[$key] = $result;
  }

  return $pairs;
}

function get_disabled_exchange_pairs() {
  $pairs = array();

  // add all discovered pairs
  foreach (Exchanges::getAllInstances() as $key => $exchange) {
    // only disabled exchanges
    if (!in_array($key, Exchanges::getDisabled())) {
      continue;
    }

    $persistent = new \Core\PersistentExchange($exchange, db());
    $result = array();
    foreach ($persistent->getMarkets() as $pair) {
      if (in_array($pair[0], get_all_currencies()) && in_array($pair[1], get_all_currencies())) {
        $result[] = $pair;
      }
    }

    $pairs[$key] = $result;
  }

  return $pairs;
}

$_cached_get_new_exchange_pairs = null;
/**
 * Get all exchange pairs that can be considered 'new'.
 * May be cached.
 */
function get_new_exchange_pairs() {
  global $_cached_get_new_exchange_pairs;
  if ($_cached_get_new_exchange_pairs === null) {
    $result = array();
    $q = db()->prepare("SELECT * FROM exchange_pairs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $q->execute();
    while ($pair = $q->fetch()) {
      $result[] = $pair['exchange'] . "_" . $pair['currency1'] . $pair['currency2'];
    }
    $_cached_get_new_exchange_pairs = $result;
  }
  return $_cached_get_new_exchange_pairs;
}

$_cached_get_security_exchange_pairs = null;
/**
 * Includes disabled exchanges
 * May be cached.
 */
function get_security_exchange_pairs() {
  global $_cached_get_new_exchange_pairs;
  if ($_cached_get_new_exchange_pairs === null) {
    $result = array();
    $q = db()->prepare("SELECT exchange, currency FROM security_exchange_securities GROUP BY exchange, currency");
    $q->execute();
    while ($currency = $q->fetch()) {
      if (!isset($result[$currency['exchange']])) {
        $result[$currency['exchange']] = array();
      }
      $result[$currency['exchange']][] = $currency['currency'];
    }
    $_cached_get_new_exchange_pairs = $result;
  }
  return $_cached_get_new_exchange_pairs;
}

/**
 * Includes disabled exchanges
 */
function get_security_exchange_tables() {
  return array(
    "litecoinglobal" => "securities_litecoinglobal",  // issue #93: this is now disabled
    "btct" => "securities_btct",            // issue #93: this is now disabled
    "cryptostocks" => "securities_cryptostocks",
    "havelock" => "securities_havelock",
    "bitfunder" => "securities_bitfunder",        // this is now disabled
    "crypto-trade" => "securities_cryptotrade",
    "796" => "securities_796",
    "litecoininvest" => "securities_litecoininvest",
    "btcinve" => "securities_btcinve",
  );
}

function get_new_security_exchanges() {
  return array("litecoininvest");
}

/**
 * Does not include disabled accounts or exchanges
 */
function get_supported_wallets() {
  $wallets = array(
    // alphabetically sorted, except for generic
    "796" => array('btc', 'ltc', 'usd'),
    "cryptostocks" => array('btc', 'ltc'),
    "litecoininvest" => array('ltc'),
    "generic" => get_all_currencies(),
  );

  // add all discovered pairs
  foreach (Accounts::getAllInstances() as $key => $exchange) {
    if (in_array($key, Accounts::getDisabled())) {
      // do not list disabled accounts
      continue;
    }
    $persistent = new \Core\PersistentAccountType($exchange, db());
    $result = array();
    foreach ($persistent->getSupportedCurrencies() as $currency) {
      if (in_array($currency, get_all_currencies())) {
        $result[] = $currency;
      }
    }

    $wallets[$key] = $result;
  }

  // and add in hash currencies (temporary; eventually we want to remove 'hash' from this return result)
  foreach (Accounts::getMiners() as $key) {
    if (in_array($key, Accounts::getDisabled())) {
      // do not list disabled accounts
      continue;
    }
    $wallets[$key][] = 'hash';
  }

  return $wallets;
}


$_cached_get_new_supported_wallets = null;
/**
 * Get all wallets that can be considered 'new'
 */
function get_new_supported_wallets() {
  global $_cached_get_new_supported_wallets;
  if ($_cached_get_new_supported_wallets === null) {
    $result = array();
    $q = db()->prepare("SELECT * FROM account_currencies WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $q->execute();
    while ($pair = $q->fetch()) {
      $result[] = $pair['exchange'];
    }
    $_cached_get_new_supported_wallets = $result;
  }
  return $_cached_get_new_supported_wallets;
}

function get_summary_types() {
  // add cryptocurrencies and commodity currencies automatically
  $summary_types = array();
  foreach (get_all_cryptocurrencies() as $cur) {
    $summary_types['summary_' . $cur] = array(
      'currency' => $cur,
      'key' => $cur,
      'title' => get_currency_name($cur),
      'short_title' => get_currency_abbr($cur),
    );
  }
  foreach (get_all_commodity_currencies() as $cur) {
    $summary_types['summary_' . $cur] = array(
      'currency' => $cur,
      'key' => $cur,
      'title' => get_currency_name($cur),
      'short_title' => get_currency_abbr($cur),
    );
  }

  // add fiat pairs automatically
  foreach (get_exchange_pairs() as $exchange => $pairs) {
    if (in_array($exchange, Exchanges::getDisabled())) {
      // ignore disabled exchanges
      continue;
    }

    foreach ($pairs as $pair) {
      if ($pair[1] == 'btc') {
        // fiat currency
        $summary_types['summary_' . $pair[0] . '_' . $exchange] = array(
          'currency' => $pair[0],
          'key' => $pair[0] . '_' . $exchange,
          'title' => get_currency_name($pair[0]) . ' (converted through ' . get_exchange_name($exchange) . ')',
          'short_title' => get_currency_abbr($pair[0]) . ' (' . get_exchange_name($exchange) . ')',
          'exchange' => $exchange,
        );
      }
    }
  }

  // finally, add market averages for fiats
  // (if there is a result in the ticker_recent)
  foreach (get_all_fiat_currencies() as $cur) {
    $exchange = "average";
    $q = db()->prepare("SELECT * FROM ticker_recent WHERE currency1=? AND currency2=? AND exchange=? LIMIT 1");
    $q->execute(array($cur, 'btc', 'average'));
    if ($q->fetch()) {
      $summary_types['summary_' . $cur . '_' . $exchange] = array(
        'currency' => $cur,
        'key' => $cur . '_' . $exchange,
        'title' => get_currency_name($cur) . ' (converted using market average)',
        'short_title' => get_currency_abbr($cur) . ' (market average)',
        'exchange' => $exchange,
      );
    }
  }

  return $summary_types;

}

// used in graphs/util.php for defining BTC equivalent defaults
// also used in wizard_currencies.php for default exchanges
// TODO use in other graphs for default exchanges
function get_default_currency_exchange($c) {
  switch ($c) {
    // cryptos
    case "ltc": return "btce";
    case "ftc": return "btce";
    case "ppc": return "btce";
    case "nmc": return "btce";
    case "nvc": return "btce";
    case "xpm": return "cryptsy";
    case "trc": return "cryptsy";
    case "dog": return "coins-e";
    case "mec": return "cryptsy";
    case "xrp": return "justcoin_anx";
    case "dgc": return "cryptsy";
    case "wdc": return "cryptsy";
    case "ixc": return "cryptsy";
    case "vtc": return "cryptsy";
    case "net": return "cryptsy";
    case "hbn": return "cryptsy";
    case "bc1": return "cryptsy";
    case "drk": return "cryptsy";
    case "vrc": return "bittrex";
    case "nxt": return "cryptsy";
    case "rdd": return "cryptsy";
    case "via": return "cryptsy";
    case "nbt": return "bter";
    case "nsr": return "bter";
    case "sj1": return "poloniex";
    // fiats
    case "usd": return "bitstamp";
    case "nzd": return "bitnz";
    case "eur": return "btce";
    case "gbp": return "coinbase";
    case "aud": return "coinbase";
    case "cad": return "virtex";
    case "cny": return "btcchina";
    case "pln": return "bitcurex";
    case "ils": return "bit2c";
    case "krw": return "kraken";
    case "sgd": return "itbit";
    case "dkk": return "coinbase";
    case "inr": return "coinbase";
    // commodities
    case "ghs": return "cexio";
    default: throw new Exception("Unknown currency to exchange into: $c");
  }
}

/**
 * Total conversions: all currencies to a single currency, where possible.
 * (e.g. there's no exchange defined yet that converts NZD -> USD)
 */
$global_get_total_conversion_summary_types = null;
function get_total_conversion_summary_types() {
  global $global_get_total_conversion_summary_types;
  if ($global_get_total_conversion_summary_types == null) {
    $summary_types = array();

    // add fiat pairs automatically
    foreach (get_exchange_pairs() as $exchange => $pairs) {
      foreach ($pairs as $pair) {
        if ($pair[1] == 'btc') {
          // fiat currency
          $summary_types[$pair[0] . '_' . $exchange] = array(
            'currency' => $pair[0],
            'title' => get_currency_name($pair[0]) . ' (converted through ' . get_exchange_name($exchange) . ')',
            'short_title' => get_currency_abbr($pair[0]) . ' (' . get_exchange_name($exchange) . ')',
            'exchange' => $exchange,
          );
        }
      }
    }

    // and also all average pairs for all fiats
    // (if there is a result in the ticker_recent)
    foreach (get_all_fiat_currencies() as $cur) {
      $exchange = "average";
      $q = db()->prepare("SELECT * FROM ticker_recent WHERE currency1=? AND currency2=? AND exchange=? LIMIT 1");
      $q->execute(array($cur, 'btc', 'average'));
      if ($q->fetch()) {
        $summary_types[$cur . '_' . $exchange] = array(
          'currency' => $cur,
          'title' => get_currency_name($cur) . ' (converted using market average)',
          'short_title' => get_currency_abbr($cur) . ' (market average)',
          'exchange' => $exchange,
        );
      }
    }

    // sort by currency order, then title
    uasort($summary_types, 'sort_get_total_conversion_summary_types');

    $global_get_total_conversion_summary_types = $summary_types;
  }
  return $global_get_total_conversion_summary_types;
}

function sort_get_total_conversion_summary_types($a, $b) {
  $order_a = array_search($a['currency'], get_all_currencies());
  $order_b = array_search($b['currency'], get_all_currencies());
  if ($order_a == $order_b) {
    return strcmp($a['short_title'], $b['short_title']);
  }
  return $order_a - $order_b;
}

/**
 * Crypto conversions: all cryptocurrencies to a single currency.
 */
function get_crypto_conversion_summary_types() {
  $currencies = get_all_cryptocurrencies() + get_all_commodity_currencies();
  $result = array();
  foreach ($currencies as $c) {
    $result[$c] = array(
      'currency' => $c,
      'title' => get_currency_name($c),
      'short_title' => get_currency_abbr($c),
    );
  }
  return $result;
}

function safe_table_name($s) {
  return str_replace("-", "", $s);
}

/**
 * Return a grouped array of (job_type => (table, gruop, wizard, failure, ...))
 */
function account_data_grouped() {
  $addresses_data = array();
  $mining_pools_data = array();
  $security_exchange_wallets_data = array();
  $exchange_wallets_data = array();
  $security_exchange_ticker_data = array();

  // we can generate this automatically
  foreach (get_address_currencies() as $cur) {
    $addresses_data["address_" . $cur] = array(
      'title' => get_currency_abbr($cur) . ' addresses',
      'label' => 'address',
      'labels' => 'addresses',
      'table' => 'addresses',
      'group' => 'addresses',
      'query' => " AND currency='$cur'",
      'wizard' => 'addresses',
      'currency' => $cur,
      'job_type' => 'addresses_' . $cur,
    );
  }

  foreach (Accounts::getKeys() as $exchange) {
    if (in_array($exchange, Accounts::getMiners())) {
      // a miner
      $mining_pools_data[$exchange] = array(
        'table' => 'accounts_' . safe_table_name($exchange),
        'group' => 'accounts',
        'wizard' => 'pools',
        'failure' => true,
        'disabled' => in_array($exchange, Accounts::getDisabled()),
        'job_type' => 'account_' . $exchange,
      );
    } else if (in_array($exchange, Accounts::getSecurityExchanges())) {
      // a security exchange
      $security_exchange_wallets_data[$exchange] = array(
        'table' => 'accounts_' . safe_table_name($exchange),
        'group' => 'accounts',
        'wizard' => 'securities',
        'failure' => true,
        'disabled' => in_array($exchange, Accounts::getDisabled()),
        'job_type' => 'account_' . $exchange,
      );
    } else {
      // otherwise, assume an exchange wallet
      $exchange_wallets_data[$exchange] = array(
        'table' => 'accounts_' . safe_table_name($exchange),
        'group' => 'accounts',
        'wizard' => 'exchanges',
        'failure' => true,
        'disabled' => in_array($exchange, Accounts::getDisabled()),
        'job_type' => 'account_' . $exchange,
      );
    }
  }

  // not sure what this used for, if anything
  foreach (SecurityExchanges::getKeys() as $cur) {
    $security_exchange_ticker_data['securities_' . $cur] = array(
      'title' => get_currency_abbr($cur) . ' securities',
      'label' => 'security',
      'labels' => 'securities',
      'table' => 'security_exchange_securities',
      'query' => " AND exchange='$cur'",
      'exchange' => $cur,
      'job_type' => 'securities_' . $cur,
    );
  }

  $data = array(
    'Addresses' /* i18n */ => $addresses_data,
    'Mining pools' /* i18n */ => $mining_pools_data,
    'Exchanges' /* i18n */ => $exchange_wallets_data,
    'Securities' /* i18n */ => array_merge($security_exchange_wallets_data, array(
      '796' => array('table' => 'accounts_796', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true),
      'bitfunder' => array('table' => 'accounts_bitfunder', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true, 'disabled' => true),
      'btcinve' => array('table' => 'accounts_btcinve', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true, 'disabled' => true),
      'crypto-trade' => array('table' => 'accounts_cryptotrade', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true),
      'cryptostocks' => array('table' => 'accounts_cryptostocks', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true),
      'litecoininvest' => array('table' => 'accounts_litecoininvest', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true),
      'litecoinglobal' => array('table' => 'accounts_litecoinglobal', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true, 'disabled' => true),
    )),
    'Individual Securities' /* i18n */ => array(
      'individual_796' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_796', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => '796', 'securities_table' => 'securities_796', 'failure' => true),
      'individual_bitfunder' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_bitfunder', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'bitfunder', 'securities_table' => 'securities_bitfunder', 'failure' => true, 'disabled' => true),
      'individual_btcinve' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_btcinve', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'btcinve', 'securities_table' => 'securities_btcinve', 'failure' => true, 'disabled' => true),
      'individual_btct' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_btct', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'btct', 'securities_table' => 'securities_btct', 'failure' => true, 'disabled' => true),
      'individual_crypto-trade' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_cryptotrade', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'crypto-trade', 'securities_table' => 'securities_cryptotrade', 'failure' => true),
      'individual_cryptostocks' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_cryptostocks', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'cryptostocks', 'securities_table' => 'securities_cryptostocks', 'failure' => true),
      'individual_havelock' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_havelock', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'havelock', 'securities_table' => 'securities_havelock', 'failure' => true),
      'individual_litecoininvest' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_litecoininvest', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'litecoininvest', 'securities_table' => 'securities_litecoininvest', 'failure' => true),
      'individual_litecoinglobal' => array('label' => 'security', 'labels' => 'securities', 'table' => 'accounts_individual_litecoinglobal', 'group' => 'accounts', 'wizard' => 'individual', 'exchange' => 'litecoinglobal', 'securities_table' => 'securities_litecoinglobal', 'failure' => true, 'disabled' => true),
    ),
    'Securities Tickers' /* i18n */ => array_merge($security_exchange_ticker_data, array(
      'securities_796' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_796', 'exchange' => '796', 'securities_table' => 'securities_796'),
      'securities_bitfunder' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_bitfunder', 'exchange' => 'bitfunder', 'securities_table' => 'securities_bitfunder', 'disabled' => true),
      'securities_btcinve' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_btcinve', 'exchange' => 'btcinve', 'securities_table' => 'securities_btcinve', 'disabled' => true),
      'securities_btct' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_btct', 'exchange' => 'btct', 'securities_table' => 'securities_btct', 'disabled' => true),
      'securities_crypto-trade' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_cryptotrade', 'exchange' => 'crypto-trade', 'securities_table' => 'securities_cryptotrade', 'disabled' => true),
      'securities_cryptostocks' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_cryptostocks', 'exchange' => 'cryptostocks', 'securities_table' => 'securities_cryptostocks', 'disabled' => true),
      'securities_litecoininvest' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_litecoininvest', 'exchange' => 'litecoininvest', 'securities_table' => 'securities_litecoininvest'),
      'securities_litecoinglobal' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_litecoinglobal', 'exchange' => 'litecoinglobal', 'securities_table' => 'securities_litecoinglobal', 'disabled' => true),
    )),
    'Finance' /* i18n */ => array(
      'finance_accounts' => array('title' => 'Finance account', 'label' => 'finance account', 'table' => 'finance_accounts', 'group' => 'finance_accounts', 'job' => false),
      'finance_categories' => array('title' => 'Finance category', 'label' => 'finance category', 'titles' => 'finance categories', 'table' => 'finance_categories', 'group' => 'finance_categories', 'job' => false),
    ),
    'Other' /* i18n */ => array(
      'generic' => array('title' => 'Generic APIs', 'label' => 'API', 'table' => 'accounts_generic', 'group' => 'accounts', 'wizard' => 'other', 'failure' => true),
    ),
    'Hidden' /* i18n */ => array(
      'graph' => array('title' => 'Graphs', 'table' => 'graphs', 'query' => ' AND is_removed=0', 'job' => false),
      'graph_pages' => array('title' => 'Graph page', 'table' => 'graph_pages', 'group' => 'graph_pages', 'query' => ' AND is_removed=0', 'job' => false),
      'summaries' => array('title' => 'Currency summaries', 'table' => 'summaries', 'group' => 'summaries', 'job' => false),
      'notifications' => array('title' => 'Notifications', 'table' => 'notifications', 'group' => 'notifications', 'wizard' => 'notifications'),
    ),
    'Offsets' /* i18n */ => array(
    ),
  );
  // add all offset currencies
  foreach (get_all_currencies() as $cur) {
    $data['Offsets']['offset_' . $cur] = array('title' => get_currency_name($cur), 'label' => 'offset', 'table' => 'offsets', 'group' => 'offsets', 'wizard' => 'offsets', 'query' => ' AND currency=\'' . $cur . '\'', 'currency' => $cur, 'job' => false);
  }
  foreach ($data as $key0 => $row0) {
    foreach ($row0 as $key => $row) {
      if (!isset($data[$key0][$key]['label'])) {
        $data[$key0][$key]['label'] = "account";
      }
      if (!isset($data[$key0][$key]['labels'])) {
        $data[$key0][$key]['labels'] = $data[$key0][$key]['label'] . "s";
      }
      if (!isset($data[$key0][$key]['title'])) {
        $data[$key0][$key]['title'] = get_exchange_name($key) . (isset($row['suffix']) ? $row['suffix'] : "") . " " . $data[$key0][$key]['labels'];
      }
      if (!isset($data[$key0][$key]['title_key'])) {
        $data[$key0][$key]['title_key'] = $key;
      }
      if (!isset($data[$key0][$key]['failure'])) {
        $data[$key0][$key]['failure'] = false;
      }
      if (!isset($data[$key0][$key]['job'])) {
        $data[$key0][$key]['job'] = true;
      }
      if (!isset($data[$key0][$key]['disabled'])) {
        $data[$key0][$key]['disabled'] = false;
      }
      if (!isset($data[$key0][$key]['suffix'])) {
        $data[$key0][$key]['suffix'] = false;
      }
      if (!isset($data[$key0][$key]['system'])) {
        $data[$key0][$key]['system'] = false;
      }
      if (!isset($data[$key0][$key]['query'])) {
        $data[$key0][$key]['query'] = '';
      }
      if (!isset($data[$key0][$key]['job_type']) && isset($data[$key0][$key]['exchange'])) {
        $data[$key0][$key]['job_type'] = $data[$key0][$key]['exchange'];
      }
      if (!isset($data[$key0][$key]['job_type']) && $key0 == "Exchanges") {
        $data[$key0][$key]['job_type'] = $key;
      }
    }
  }
  return $data;
}

/**
 * @return the account data as an array, or {@code false} if no account type could be found (and {@code throw_exception_on_failure} is {@code false})
 * @throws Exception if {@code throw_exception_on_failure} is {@code true} and no account type could be found
 */
function get_account_data($exchange, $throw_exception_on_failure = true) {
  foreach (account_data_grouped() as $group => $data) {
    foreach ($data as $key => $values) {
      if ($key == $exchange) {
        return $values;
      }
    }
  }
  if ($throw_exception_on_failure) {
    throw new Exception("Could not find any exchange '$exchange'");
  } else {
    return false;
  }
}

// we can't get this from account_data_grouped() because this also includes ticker information
function get_external_apis() {
  $external_apis_addresses = array();
  foreach (Currencies::getBalanceCurrencies() as $key) {
    $currency = Currencies::getInstance($key);
    if ($currency->getExplorerURL()) {
      $link = link_to($currency->getExplorerURL(), $currency->getExplorerName());
    } else {
      $link = htmlspecialchars($currency->getExplorerName());
    }

    $external_apis_addresses["address_" . $key] = array(
      'link' => $link,
      'package' => Currencies::getDefiningPackage($key),
    );
  }

  $external_apis_blockcounts = array();
  foreach (Currencies::getBlockCurrencies() as $key) {
    $currency = Currencies::getInstance($key);
    if ($currency->getExplorerURL()) {
      $link = link_to($currency->getExplorerURL(), $currency->getExplorerName());
    } else {
      $link = htmlspecialchars($currency->getExplorerName());
    }

    $external_apis_blockcounts["blockcount_" . $key] = array(
      'link' => $link,
      'package' => Currencies::getDefiningPackage($key),
    );
  }

  $exchange_tickers = array();
  foreach (Exchanges::getAllInstances() as $key => $exchange) {
    if (in_array($key, Exchanges::getDisabled())) {
      // do not list disabled exchanges
      continue;
    }
    $link = link_to($exchange->getURL(), $exchange->getName());
    $exchange_tickers["ticker_" . $key] = array(
      'link' => $link,
      'package' => Exchanges::getDefiningPackage($key),
    );
  }

  $mining_pools = array();
  $exchange_wallets = array();
  foreach (Accounts::getKeys() as $key) {
    if (in_array($key, Accounts::getDisabled())) {
      // do not list disabled accounts
      continue;
    }
    $instance = Accounts::getInstance($key);
    if (in_array($key, Accounts::getMiners())) {
      // a miner
      $mining_pools["account_" . $key] = array(
        'link' => link_to($instance->getURL(), $instance->getName()),
        'package' => Accounts::getDefiningPackage($key),
      );
    } else {
      // otherwise, assume exchange wallet
      $exchange_wallets["account_" . $key] = array(
        'link' => link_to($instance->getURL(), $instance->getName()),
        'package' => Accounts::getDefiningPackage($key),
      );
    }
  }

  $security_exchanges_list = array();
  $security_tickers_list = array();
  foreach (SecurityExchanges::getAllInstances() as $key => $exchange) {
    if (in_array($key, Exchanges::getDisabled())) {
      // do not list disabled exchanges
      continue;
    }

    $link = link_to($exchange->getURL(), $exchange->getName());
    $security_exchanges_list["securities_" . $key] = array(
      'link' => $link,
      'package' => Exchanges::getDefiningPackage($key),
    );

    $link = link_to($exchange->getURL(), $exchange->getName());
    $security_tickers_list["security_" . $key] = array(
      'link' => $link,
      'package' => Exchanges::getDefiningPackage($key),
    );
  }

  $external_apis = array(
    "Address balances" /* i18n */ => $external_apis_addresses,

    "Block counts" /* i18n */ => $external_apis_blockcounts,

    "Mining pool wallets" /* i18n */ => $mining_pools,

    "Exchange wallets" /* i18n */ => $exchange_wallets,

    "Exchange tickers" /* i18n */ => $exchange_tickers,

    "Security exchange securities" /* i18n */ => $security_exchanges_list,

    "Security exchange tickers" /* i18n */ => $security_tickers_list,

    // TODO eventually remove these
    "Security exchanges" /* i18n */ => array(
      'securities_796' => '<a href="https://796.com">796 Xchange</a>',
      'ticker_crypto-trade' => '<a href="https://crypto-trade.com">Crypto-Trade</a>',   // securities for crypto-trade are handled by the ticker_crypto-trade
      'securities_cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a>',
      'securities_update_cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a> Securities list',
      'securities_update_havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a> Securities list',
      'securities_update_litecoininvest' => '<a href="https://litecoininvest.com">Litecoininvest</a> Securities list',

      // moved from Exchange Wallets, because this is now generated automatically
      'cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a>',
      'havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a>',
      'litecoininvest' => '<a href="https://litecoininvest.com">Litecoininvest</a>',
    ),

    "Individual securities" /* i18n */ => array(
      'individual_crypto-trade' => '<a href="https://crypto-trade.com">Crypto-Trade</a>',
      'individual_cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a>',
      'individual_havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a>',
      'individual_litecoininvest' => '<a href="https://litecoininvest.com">Litecoininvest</a>',
    ),

    "Other" /* i18n */ => array(
      // 'generic' => "Generic API balances",
      'outstanding' => '<a href="' . htmlspecialchars(url_for('premium')) . '">Premium account</a> processing',
    ),
  );

  // convert to new format
  foreach ($external_apis as $group => $data) {
    foreach ($data as $key => $value) {
      if (!is_array($value)) {
        $external_apis[$group][$key] = array('link' => $value);
      }
    }
  }

  return $external_apis;
}

/**
 * Return a list of external API status keys to external API status titles;
 * titles are obtained by stripping HTML. It might be better to refactor this
 * so that titles are the default and HTML is added later.
 */
function get_external_apis_titles() {
  $apis = get_external_apis();
  $result = array();
  foreach ($apis as $group => $data) {
    foreach ($data as $key => $title) {
      $result[$key] = preg_replace('#<[^>]+?>#im', '', $title['link']) . translate_external_api_group_to_suffix($group) . " API";
    }
  }
  // sort by title
  asort($result);
  return $result;
}

function translate_external_api_group_to_suffix($group) {
  // TODO use keys, not text
  switch ($group) {
    case "Address balances":
      return "";

    case "Mining pool wallets":
    case "Exchange wallets":
      return " " . t("wallet");

    case "Exchange tickers":
      return " " . t("ticker");

    case "Other":
      return "";  // nothing

    default:
      return "";  // nothing
  }
}

/**
 * Wraps {@link Currency}s to return data expected of
 * {@link #get_blockchain_wizard_config()}.
 */
class BlockchainWizardConfig {
  function __construct(Currency $currency) {
    $this->currency = $currency;
  }

  function getConfig() {
    $result = array(
      'premium_group' => 'address_' . $this->currency->getCode(),
      'title' => $this->currency->getAbbr() . ' address',
      'titles' => $this->currency->getAbbr() . ' addresses',
      'table' => 'addresses',
      'currency' => $this->currency->getCode(),
      'callback' => array($this->currency, 'isValid'),
      'job_type' => 'address_' . $this->currency->getCode(),
      'client' => $this->currency->getName(),
    );

    // custom knowledge base articles
    // TODO move out into components or something else
    switch ($this->currency->getCode()) {
      case "btc":
        $result["client"] = "Bitcoin-Qt";
        $result["csv_kb"] = "bitcoin_csv";
        break;

      case "ltc":
        $result["client"] = "Litecoin-Qt";
        $result["csv_kb"] = "litecoin_csv";
        break;
    }

    return $result;
  }
}

function get_blockchain_wizard_config($currency) {
  // components override
  if (Currencies::hasKey($currency)) {
    $obj = Currencies::getInstance($currency);
    $config = new BlockchainWizardConfig($obj);
    return $config->getConfig();
  }

  throw new Exception("Unknown blockchain currency '$currency'");
}

function get_accounts_wizard_config($exchange) {
  $result = get_accounts_wizard_config_basic($exchange);
  if (!isset($result['title'])) {
    $result['title'] = get_exchange_name($exchange) . " account";
  }
  if (!isset($result['titles'])) {
    $result['titles'] = $result['title'] . "s";
  }
  if (!isset($result['khash'])) {
    $result['khash'] = false;
  }
  if (!isset($result['interaction'])) {
    $result['interaction'] = false;
  }
  if (!isset($result['fixed_inputs'])) {
    $result['fixed_inputs'] = array();
  }
  foreach ($result['inputs'] as $key => $data) {
    $result['inputs'][$key]['key'] = $key;
    if (!isset($result['inputs'][$key]['inline_title'])) {
      $result['inputs'][$key]['inline_title'] = $result['inputs'][$key]['title'];
    }
  }
  foreach (account_data_grouped() as $group => $data) {
    foreach ($data as $key => $values) {
      if ($key == $exchange && isset($values['wizard'])) {
        $result['wizard'] = $values['wizard'];
      }
    }
  }
  $result['exchange'] = $exchange;
  return $result;
}

function get_accounts_wizard_config_basic($exchange) {
  switch ($exchange) {
    // --- securities ---
    case "litecoinglobal":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'Read-Only API key', 'callback' => 'is_valid_litecoinglobal_apikey'),
        ),
        'table' => 'accounts_litecoinglobal',
      );

    case "cryptostocks":
      return array(
        'inputs' => array(
          'api_email' => array('title' => 'Account e-mail', 'callback' => 'is_valid_generic_key'),
          'api_key_coin' => array('title' => 'get_coin_balances API key', 'callback' => 'is_valid_generic_key'),
          'api_key_share' => array('title' => 'get_share_balances API key', 'callback' => 'is_valid_generic_key'),
        ),
        'table' => 'accounts_cryptostocks',
      );

    case "bitfunder":
      return array(
        'inputs' => array(
          'btc_address' => array('title' => 'BTC Address', 'callback' => array(Currencies::getInstance('btc'), 'isValid')),
        ),
        'table' => 'accounts_bitfunder',
      );

    case "796":
      return array(
        'inputs' => array(
          'api_app_id' => array('title' => 'Application ID', 'callback' => 'is_numeric'),
          'api_key' => array('title' => 'API Key', 'callback' => 'is_valid_796_apikey'),
          'api_secret' => array('title' => 'API Secret', 'callback' => 'is_valid_796_apisecret'),
        ),
        'table' => 'accounts_796',
      );

    case "litecoininvest":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_litecoininvest_apikey'),
        ),
        'table' => 'accounts_litecoininvest',
      );

    case "btcinve":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_litecoininvest_apikey'),
        ),
        'table' => 'accounts_btcinve',
      );

    // --- securities ---
    case "individual_litecoinglobal":
      return array(
        'inputs' => array(
          'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
          'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_litecoinglobal_securities', 'callback' => 'is_valid_id'),
        ),
        'table' => 'accounts_individual_litecoinglobal',
      );

    case "individual_btct":
      return array(
        'inputs' => array(
          'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
          'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_btct_securities', 'callback' => 'is_valid_id'),
        ),
        'table' => 'accounts_individual_btct',
      );

    case "individual_bitfunder":
      return array(
        'inputs' => array(
          'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
          'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_bitfunder_securities', 'callback' => 'is_valid_id'),
        ),
        'table' => 'accounts_individual_bitfunder',
      );

    case "individual_cryptostocks":
      return array(
        'inputs' => array(
          'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
          'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_cryptostocks_securities', 'callback' => 'is_valid_id'),
        ),
        'table' => 'accounts_individual_cryptostocks',
      );

    case "individual_havelock":
      return array(
        'inputs' => array(
          'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
          'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_havelock_securities', 'callback' => 'is_valid_id'),
        ),
        'table' => 'accounts_individual_havelock',
      );

    case "individual_crypto-trade":
      return array(
        'inputs' => array(
          'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
          'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_cryptotrade_securities', 'callback' => 'is_valid_id'),
        ),
        'table' => 'accounts_individual_cryptotrade',
      );

    case "individual_796":
      return array(
        'inputs' => array(
          'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
          'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_796_securities', 'callback' => 'is_valid_id'),
        ),
        'table' => 'accounts_individual_796',
      );

    case "individual_litecoininvest":
      return array(
        'inputs' => array(
          'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
          'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_litecoininvest_securities', 'callback' => 'is_valid_id'),
        ),
        'table' => 'accounts_individual_litecoininvest',
      );

    case "individual_btcinve":
      return array(
        'inputs' => array(
          'quantity' => array('title' => t('Quantity'), 'callback' => 'is_valid_quantity'),
          'security_id' => array('title' => t('Security'), 'dropdown' => 'dropdown_get_btcinve_securities', 'callback' => 'is_valid_id'),
        ),
        'table' => 'accounts_individual_btcinve',
      );

    // --- other ---
    case "generic":
      return array(
        'inputs' => array(
          'api_url' => array('title' => 'URL', 'callback' => 'is_valid_generic_url', 'length' => 255),
          'currency' => array('title' => t('Currency'), 'dropdown' => 'dropdown_currency_list', 'callback' => 'is_valid_currency', 'style_prefix' => 'currency_name_'),
          'multiplier' => array('title' => t('Multiplier'), 'inline_title' => t('value'), 'callback' => 'is_numeric', 'length' => 6, 'default' => 1, 'number' => true),
        ),
        'table' => 'accounts_generic',
        'title' => 'Generic API',
      );

    default:
      // --- discovered accounts ---
      if (Accounts::hasKey($exchange)) {
        $account = Accounts::getInstance($exchange);

        $inputs = array();
        foreach ($account->getFields() as $key => $field) {
          $inputs[$key] = array(
            'title' => $field['title'],
            'callback' => array(new AccountFieldCheck($field), 'check'),
          );

          if (isset($field['type']) && $field['type'] == "confirm") {
            $inputs[$key]['checkbox'] = true;
          }

          if (isset($field['note']) && $field['note']) {
            $inputs[$key]['note'] = t($field['note'][0], $field['note'][1]);
          }

          if (isset($field['interaction']) && $field['interaction']) {
            $inputs[$key]['interaction'] = $field['interaction'];
          }
        }

        return array(
          'inputs' => $inputs,
          'table' => 'accounts_' . safe_table_name($exchange),
          'interaction' => ($account instanceof \Account\UserInteractionAccount) ? array($account, 'interaction') : false,
        );
      }

      // --- offsets ---
      if (substr($exchange, 0, strlen("offset_")) === "offset_") {
        $cur = substr($exchange, strlen("offset_"));
        return array(
          'inputs' => array(
            'balance' => array('title' => t('Value'), 'inline_title' => t('value'), 'callback' => 'is_numeric', 'length' => 6, 'default' => 1, 'number' => true),
          ),
          'fixed_inputs' => array(
            'currency' => $cur,
          ),
          'table' => 'offsets',
          'title' => get_currency_abbr($cur) . " offset",
        );
      }

      throw new Exception("Unknown accounts type '$exchange'");
  }
}

/**
 * Helper class to implement field checks for account field types.
 */
class AccountFieldCheck {
  function __construct($field) {
    $this->field = $field;
  }

  function check($value) {
    if (isset($this->field['regexp'])) {
      if (!preg_match($this->field['regexp'], $value)) {
        return false;
      }
    }
    return true;
  }
}

// this function is in crypto.php so we can use just one wizard_accounts_callback rather than needing wizard_accounts_exchanges_callback (etc)
function get_wizard_account_type($wizard) {
  switch ($wizard) {
    // this should only be used for transaction creators!
    case "addresses":
      $account_type = array(
        'title' => t('Address'),
        'titles' => t('Addresses'),
        'wizard' => 'addresses',
        'transaction_creation' => true,
      );
      break;

    // this should only be used for transaction creators!
    case "notifications":
      $account_type = array(
        'title' => t('Notification'),
        'titles' => t('Notifications'),
        'wizard' => 'notifications',
        'transaction_creation' => false,
      );
      break;

    case "exchanges":
      $account_type = array(
        'title' => t('Exchange'),
        'titles' => t('Exchanges'),
        'wizard' => 'exchanges',
        'hashrate' => false,
        'has_balances' => true,
        'url' => 'wizard_accounts_exchanges',
        'add_help' => 'add_service',
        'a' => 'an',
        'transaction_creation' => true,
        'can_test' => true,
      );
      break;

    case "pools":
      $account_type = array(
        'title' => t('Mining Pool'),
        'titles' => t('Mining Pools'),
        'wizard' => 'pools',
        'hashrate' => true,
        'has_balances' => true,
        'url' => 'wizard_accounts_pools',
        'add_help' => 'add_service',
        'transaction_creation' => true,
        'can_test' => true,
      );
      break;

    case "securities":
      $account_type = array(
        'title' => t('Securities Exchange'),
        'titles' => t('Securities Exchanges'),
        'wizard' => 'securities',
        'hashrate' => false,
        'has_balances' => true,
        'url' => 'wizard_accounts_securities',
        'add_help' => 'add_service',
        'can_test' => true,
      );
      break;

    case "individual":
      $account_type = array(
        'title' => t('Individual Security'),
        'titles' => t('Individual Securities'),
        'accounts' => 'securities',
        'wizard' => 'individual',
        'hashrate' => false,
        'has_balances' => true,
        'url' => 'wizard_accounts_individual_securities',
        'first_heading' => t('Exchange'),
        'display_headings' => array('security' => t('Security'), 'quantity' => t('Quantity')),
        'display_callback' => 'get_individual_security_config',
        'add_help' => 'add_service',
        'a' => 'an',
        'can_test' => true,
      );
      break;

    case "offsets":
      $account_type = array(
        'title' => t('Offset'),
        'titles' => t('Offsets'),
        'wizard' => 'offsets',
        'hashrate' => false,
        'url' => 'wizard_accounts_offsets',
        'first_heading' => t('Currency'),
        'display_headings' => array('balance' => t('Amount')),
        'display_editable' => array('balance' => 'number_format_autoprecision'),
        'a' => 'an',
        'exchange_name_callback' => 'get_currency_name_from_exchange',
        'help_filename_callback' => 'get_offsets_help_filename',
        'add_label' => t('Add offset'),
      );
      break;

    case "other":
      $account_type = array(
        'title' => t('Other Account'),
        'titles' => t('Other Accounts'),
        'wizard' => 'other',
        'hashrate' => false,
        'has_balances' => true,
        'url' => 'wizard_accounts_other',
        'add_help' => 'add_service',
        'a' => 'an',
        'display_headings' => array('multiplier' => t('Multiplier')),
        'display_editable' => array('multiplier' => 'number_format_autoprecision'),
        'transaction_creation' => true,
        'can_test' => true,
      );
      break;

    default:
      throw new Exception("Unknown wizard type '" . htmlspecialchars($wizard) . "'");
  }

  if (!isset($account_type['display_headings'])) {
    $account_type['display_headings'] = array();
  }
  if (!isset($account_type['display_callback'])) {
    $account_type['display_callback'] = false;
  }
  if (!isset($account_type['display_editable'])) {
    $account_type['display_editable'] = array();
  }
  if (!isset($account_type['first_heading'])) {
    $account_type['first_heading'] = $account_type['title'];
  }
  if (!isset($account_type['accounts'])) {
    $account_type['accounts'] = "accounts";
  }
  if (!isset($account_type['add_label'])) {
    $account_type['add_label'] = t('Add account');
  }
  if (!isset($account_type['a'])) {
    $account_type['a'] = "a";
  }
  if (!isset($account_type['transaction_creation'])) {
    $account_type['transaction_creation'] = false;
  }
  if (!isset($account_type['has_balances'])) {
    $account_type['has_balances'] = false;
  }
  if (!isset($account_type['has_transactions'])) {
    $account_type['has_transactions'] = false;
  }
  if (!isset($account_type['can_test'])) {
    $account_type['can_test'] = false;
  }
  if (!isset($account_type['exchange_name_callback'])) {
    $account_type['exchange_name_callback'] = 'get_exchange_name';
  }
  if (!isset($account_type['help_filename_callback'])) {
    $account_type['help_filename_callback'] = 'get_exchange_help_filename';
  }

  return $account_type;
}

function get_currency_name_from_exchange($s) {
  return get_currency_abbr(substr($s, strlen("offset_")));
}
function get_exchange_help_filename($s) {
  return "inline_accounts_" . $s;
}
function get_offsets_help_filename($s) {
  return "inline_offsets";
}

function get_individual_security_config($account) {
  $security = "(unknown exchange)";
  $securities = false;
  $historical_key = false;    // used to link from wizard_accounts_individual_securities to historical
  switch ($account['exchange']) {
    case "individual_litecoinglobal":
      $securities = dropdown_get_litecoinglobal_securities();
      $historical_key = 'securities_litecoinglobal_ltc';
      break;
    case "individual_btct":
      $securities = dropdown_get_btct_securities();
      $historical_key = 'securities_btct_btc';
      break;
    case "individual_bitfunder":
      $securities = dropdown_get_bitfunder_securities();
      $historical_key = 'securities_bitfunder_btc';
      break;
    case "individual_havelock":
      $securities = dropdown_get_havelock_securities();
      $historical_key = 'securities_havelock_btc';
      break;
    case "individual_cryptostocks":
      $securities = dropdown_get_cryptostocks_securities();
      break;
    case "individual_crypto-trade":
      $securities = dropdown_get_cryptotrade_securities();
      break;
    case "individual_796":
      $securities = dropdown_get_796_securities();
      $historical_key = 'securities_796_btc';
      break;
    case "individual_litecoininvest":
      $securities = dropdown_get_litecoininvest_securities();
      $historical_key = 'securities_litecoininvest_ltc';
      break;
    case "individual_btcinve":
      $securities = dropdown_get_btcinve_securities();
      $historical_key = 'securities_btcinve_btc';
      break;
  }

  if ($securities) {
    if (isset($securities[$account['security_id']])) {
      if ($historical_key) {
        $security = "<a href=\"" . htmlspecialchars(url_for('historical', array('id' => $historical_key, 'days' => 180, 'name' => $securities[$account['security_id']]))) . "\">" . htmlspecialchars($securities[$account['security_id']]) . "</a>";
      } else {
        $security = htmlspecialchars($securities[$account['security_id']]);
      }
    } else {
      $security = "(unknown security " . htmlspecialchars($account['security_id']) . ")";
    }
  }

  return array(
    $security,
    number_format($account['quantity']),
  );
}

function get_default_openid_providers() {
  return array(
    'google' => array('Google Accounts', 'https://www.google.com/accounts/o8/id'),
    'stackexchange' => array('StackExchange', 'https://openid.stackexchange.com'),
    'yahoo' => array('Yahoo', 'https://me.yahoo.com'),
    'blogspot' => array('Blogspot', 'https://www.blogspot.com/'),
    'verisign' => array('Symantec PIP', 'https://pip.verisignlabs.com/'),
    'launchpad' => array('Launchpad', 'https://login.launchpad.net/'),
    'aol' => array('AOL', 'https://openid.aol.com/'),
  );
}

/**
 * A helper function to match (OpenID URLs) to default OpenID providers.
 * Each URL is matched as a regexp.
 */
function get_openid_provider_formats() {
  return array(
    '#^https?://www.google.com/accounts/#im' => 'google',
    // '#^https?://profiles.google.com/#im' => 'google-plus',
    '#^https?://openid.stackexchange.com/#im' => 'stackexchange',
    '#^https?://openid.aol.com/#im' => 'aol',
    '#^https?://me.yahoo.com/#im' => 'yahoo',
    // '#^https?://[^\\.]+.myopenid.com/#im' => 'myopenid',
    '#^https?://[^\\.]+.verisignlabs.com/#im' => 'verisign',
    // '#^https?://[^\\.]+.wordpress.com/#im' => 'wordpress',
    '#^https?://[^\\.]+.blogspot.com/#im' => 'blogspot',
    '#^https?://launchpad.net/~#im' => 'launchpad',
    '#^https?://login.launchpad.net/\\+#im' => 'launchpad',
  );
}

function get_permitted_days() {
  $permitted_days = array(
    '45' => array('title' => '45 days', 'days' => 45),
    '90' => array('title' => '90 days', 'days' => 90),
    '180' => array('title' => '180 days', 'days' => 180),
    'year' => array('title' => '1 year', 'days' => 366),    // TODO get rid of 'year', use 366 instead!
    '2year' => array('title' => '2 years', 'days' => 366*2),    // TODO get rid of 'year', use 366 instead!
  );
  return $permitted_days;
}

function get_permitted_notification_periods() {
  return array(
    'hour' => array('label' => t('the last hour'), 'title' => 'hourly', 'interval' => 'INTERVAL 1 HOUR'),
    'day' => array('label' => t('the last day'), 'title' => 'daily', 'interval' => 'INTERVAL 1 DAY'),
    'week' => array('label' => t('the last week'), 'title' => 'weekly', 'interval' => 'INTERVAL 1 WEEK'),
    'month' => array('label' => t('the last month'), 'title' => 'monthly', 'interval' => 'INTERVAL 1 MONTH'),
  );
}

function get_permitted_notification_conditions() {
  return array(
    'increases_by' => t("increases by"),
    'increases' => t("increases"),
    'above' => t("is above"),
    'decreases_by' => t("decreases by"),
    'decreases' => t("decreases"),
    'below' => t("is below"),
  );
}

function get_permitted_deltas() {
  $permitted_days = array(
    '' => array('title' => 'value', 'description' => t('None')),
    'absolute' => array('title' => 'change', 'description' => t('Change')),
    'percent' => array('title' => 'percent', 'description' => t('% change')),
  );
  return $permitted_days;
}

$_latest_tickers = array();
/**
 * Get the latest ticker value for the given exchange and currency pairs.
 * Allows for caching these values.
 * @returns false if no ticker value could be found.
 */
function get_latest_ticker($exchange, $cur1, $cur2) {
  $key = $exchange . '_' . $cur1 . '_' . $cur2;
  global $_latest_tickers;
  if (!isset($_latest_tickers[$key])) {
    $latest_tickers[$key] = false;
    $q = db()->prepare("SELECT * FROM ticker_recent WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 LIMIT 1");
    $q->execute(array(
      "exchange" => $exchange,
      "currency1" => $cur1,
      "currency2" => $cur2,
    ));
    if ($ticker = $q->fetch()) {
      set_latest_ticker($ticker);
    }
  }
  return isset($_latest_tickers[$key]) ? $_latest_tickers[$key] : false;
}
// used for testing
function set_latest_ticker($ticker) {
  $exchange = $ticker['exchange'];
  $cur1 = $ticker['currency1'];
  $cur2 = $ticker['currency2'];
  $key = $exchange . '_' . $cur1 . '_' . $cur2;
  global $_latest_tickers;
  $_latest_tickers[$key] = $ticker;
}

/**
 * Reset currencies, graph data etc to their defaults.
 */
function reset_user_settings($user_id) {

  $q = db()->prepare("DELETE FROM summaries WHERE user_id=?");
  $q->execute(array($user_id));
  $q = db()->prepare("DELETE FROM summary_instances WHERE user_id=?");
  $q->execute(array($user_id));

  // default currencies
  $q = db()->prepare("INSERT INTO summaries SET user_id=?,summary_type=?");
  $q->execute(array($user_id, 'summary_btc'));
  $q = db()->prepare("INSERT INTO summaries SET user_id=?,summary_type=?");
  $q->execute(array($user_id, 'summary_usd_bitstamp'));

  $q = db()->prepare("UPDATE users SET preferred_crypto=?, preferred_fiat=? WHERE id=?");
  $q->execute(array('btc', 'usd', $user_id));

  reset_user_graphs($user_id);

}

function reset_user_graphs($user_id) {

  // delete all graphs and graph pages
  $q = db()->prepare("DELETE FROM graphs WHERE page_id IN (SELECT id AS page_id FROM graph_pages WHERE user_id=?)");
  $q->execute(array($user_id));

  $q = db()->prepare("DELETE FROM graph_pages WHERE user_id=?");
  $q->execute(array($user_id));

  // set the user preferences to 'auto'
  // and request graph updating
  $q = db()->prepare("UPDATE users SET needs_managed_update=1, graph_managed_type=? WHERE id=?");
  $q->execute(array('auto', $user_id));

}

/**
 * Just returns an array of ('ltc' => 'LTC', 'btc' => 'BTC', ...)
 */
function dropdown_currency_list() {
  $result = array();
  foreach (get_all_currencies() as $c) {
    $result[$c] = get_currency_abbr($c);
  }
  return $result;
}

function dropdown_get_litecoinglobal_securities() {
  return dropdown_get_all_securities('litecoinglobal');
}

function dropdown_get_btct_securities() {
  return dropdown_get_all_securities('btct');
}

function dropdown_get_bitfunder_securities() {
  return dropdown_get_all_securities('bitfunder');
}

function dropdown_get_cryptostocks_securities() {
  return dropdown_get_all_securities('cryptostocks');
}

function dropdown_get_havelock_securities() {
  return dropdown_get_all_securities('havelock');
}

function dropdown_get_cryptotrade_securities() {
  return dropdown_get_all_securities('cryptotrade');
}

function dropdown_get_796_securities() {
  return dropdown_get_all_securities('796');
}

function dropdown_get_litecoininvest_securities() {
  return dropdown_get_all_securities('litecoininvest');
}

function dropdown_get_btcinve_securities() {
  return dropdown_get_all_securities('btcinve');
}

/**
 * Returns an array of (id => security name).
 * Cached across calls.
 */
$dropdown_get_all_securities = array();
function dropdown_get_all_securities($exchange) {
  global $dropdown_get_all_securities;
  if (!isset($dropdown_get_all_securities[$exchange])) {
    $dropdown_get_all_securities[$exchange] = array();
    $q = db()->prepare("SELECT * FROM security_exchange_securities WHERE exchange=?");
    $q->execute(array($exchange));
    while ($sec = $q->fetch()) {
      $dropdown_get_all_securities[$exchange][$sec['security']] = $sec['security'];
    }
  }
  return $dropdown_get_all_securities[$exchange];
}

function is_valid_litecoinglobal_apikey($key) {
  // not sure what the format should be, seems to be 64 character hex
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_generic_key($key) {
  // this could probably be in any format but should be at least one character
  return strlen($key) >= 1 && strlen($key) <= 255;
}

function is_valid_cryptotrade_apikey($key) {
  // guessing the format
  return preg_match("#^[A-F0-9]{8}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{12}$#", $key);
}

function is_valid_cryptotrade_apisecret($key) {
  // looks like a 40 character hex string
  return strlen($key) == 40 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_796_apikey($key) {
  // guessing the format
  return preg_match("#^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{8}$#", $key);
}

function is_valid_796_apisecret($key) {
  // looks like a 60 character crazy string
  return strlen($key) == 60 && preg_match("#^[A-Za-z0-9\\+\\/]+$#", $key);
}

function is_valid_litecoininvest_apikey($key) {
  // looks to be lowercase hex
  return preg_match("#^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$#", $key);
}

function is_valid_currency($c) {
  return in_array($c, get_all_currencies());
}

function is_valid_generic_url($url) {
  return preg_match("#^https?://.+$#imu", $url) && strlen($url) <= 255;
}

function is_valid_name($s) {
  return mb_strlen($s) < 64;
}

function is_valid_title($s) {
  return mb_strlen($s) < 64;
}

function is_valid_quantity($n) {
  return is_numeric($n) && $n == (int) $n && $n > 0;
}

function is_valid_id($n) {
  return is_numeric($n) && $n == (int) $n && $n > 0;
}

function get_explorer_address($currency, $address) {
  foreach (\DiscoveredComponents\Currencies::getAddressCurrencies() as $cur) {
    if ($cur === $currency) {
      $instance = \DiscoveredComponents\Currencies::getInstance($cur);
      return $instance->getBalanceURL($address);
    }
  }

  foreach (get_blockchain_currencies() as $explorer => $currencies) {
    foreach ($currencies as $cur) {
      if ($cur == $currency) {
        return sprintf(get_site_config($currency . "_address_url"), $address);
      }
    }
  }
}
