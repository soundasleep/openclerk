<?php

/**
 * Defines all of the interesting properties of the web appliation:
 * what currencies are supported, what pairs, etc.
 */

use \Openclerk\Currencies\Currency;
use \DiscoveredComponents\Currencies;
use \DiscoveredComponents\Exchanges;
use \DiscoveredComponents\Accounts;

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

function get_all_hashrate_currencies() {
  // TODO actually implement HashableCurrencies
  return array("btc", "ltc", "nmc", "nvc", "dog", "ftc", "mec", "dgc", "wdc", "ixc", "vtc", "net", "hbn");
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

// return true if this currency is a SHA256 currency and measured in MH/s rather than KH/s
function is_hashrate_mhash($cur) {
  // TODO actually implement HashableCurrencies
  // if (in_array($cur, Currencies::getKeys())) {
  //   $currency = Currencies::getInstance($cur);
  //   return $currency->isMHash();
  // }
  return $cur == 'btc' || $cur == 'nmc' || $cur == 'ppc' || $cur == 'trc';
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
      "mtgox" =>      "Mt.Gox",
      "bips" =>       "BIPS",   // this is now disabled
      "litecoinglobal" =>  "Litecoin Global",
      "litecoinglobal_wallet" => "Litecoin Global (Wallet)",
      "litecoinglobal_securities" => "Litecoin Global (Securities)",
      "btct" =>       "BTC Trading Co.",
      "btct_wallet" =>    "BTC Trading Co. (Wallet)",
      "btct_securities" => "BTC Trading Co. (Securities)",
      "cryptostocks" =>   "Cryptostocks",
      "cryptostocks_wallet" => "Cryptostocks (Wallet)",
      "cryptostocks_securities" => "Cryptostocks (Securities)",
      "bitfunder"     => "BitFunder",
      "bitfunder_wallet"  => "BitFunder (Wallet)",
      "bitfunder_securities" => "BitFunder (Securities)",
      "individual_litecoinglobal" => "Litecoin Global (Individual Securities)",
      "individual_btct" => "BTC Trading Co. (Individual Securities)",
      "individual_bitfunder" => "BitFunder (Individual Securities)",
      "individual_cryptostocks" => "Cryptostocks (Individual Securities)",
      "individual_havelock" => "Havelock Investments (Individual Securities)",
      "individual_crypto-trade" => "Crypto-Trade (Individual Securities)",
      "individual_796" => "796 Xchange (Individual Securities)",
      "generic" =>    "Generic API",
      "offsets" =>    "Offsets",    // generic
      "blockchain" =>   "Blockchain", // generic
      "poolx" =>      "Pool-x.eu",
      "wemineltc" =>    "WeMineLTC",
      "wemineftc" =>    "WeMineFTC",
      "givemecoins" =>  "Give Me Coins",
      "btcguild" =>     "BTC Guild",
      "hypernova" =>    "Hypernova",
      "ltcmineru" =>    "LTCMine.ru",
      "miningforeman" =>  "Mining Foreman", // LTC default
      "miningforeman_ftc" => "Mining Foreman",
      "khore" =>      "nvc.khore.org",
      "ghashio" =>    "GHash.io",
      "crypto-trade_securities" => "Crypto-Trade (Securities)",
      "havelock" =>     "Havelock Investments",
      "havelock_wallet" => "Havelock Investments (Wallet)",
      "havelock_securities" => "Havelock Investments (Securities)",
      "bitminter" =>    "BitMinter",
      "liteguardian" =>   "LiteGuardian",
      "796" =>      "796 Xchange",
      "796_wallet" =>   "796 Xchange (Wallet)",
      "796_securities" => "796 Xchange (Securities)",
      "kattare" =>    "ltc.kattare.com",
      "litepooleu" =>   "Litepool",
      "coinhuntr" =>    "CoinHuntr",
      "eligius" =>    "Eligius",
      "lite_coinpool" =>  "lite.coin-pool.com",
      "beeeeer" =>    "b(e^5)r.org",
      "litecoinpool" => "litecoinpool.org",
      "dogepoolpw" =>   "dogepool.pw",
      "elitistjerks" => "Elitist Jerks",
      "dogechainpool" =>  "Dogechain Pool",
      "hashfaster" =>   "HashFaster", // for labels, accounts actually use hashfaster_cur
      "hashfaster_ltc" => "HashFaster",
      "hashfaster_ftc" => "HashFaster",
      "hashfaster_doge" => "HashFaster",
      "triplemining" => "TripleMining",
      "ozcoin" =>     "Ozcoin", // for labels, accounts actually use hashfaster_cur
      "ozcoin_ltc" =>   "Ozcoin",
      "ozcoin_btc" =>   "Ozcoin",
      "scryptpools" =>  "scryptpools.com",
      "bitcurex_pln" => "Bitcurex PLN", // the exchange wallet
      "bitcurex_eur" => "Bitcurex EUR", // the exchange wallet
      "justcoin" =>   "Justcoin",
      "multipool" =>    "Multipool",
      "ypool" =>      "ypool.net",
      "litecoininvest" => "Litecoininvest",
      "litecoininvest_wallet" => "Litecoininvest (Wallet)",
      "litecoininvest_securities" => "Litecoininvest (Securities)",
      "individual_litecoininvest" => "Litecoininvest (Individual Securities)",
      "btcinve" => "BTCInve",
      "btcinve_wallet" => "BTCInve (Wallet)",
      "btcinve_securities" => "BTCInve (Securities)",
      "individual_btcinve" => "BTCInve (Individual Securities)",
      "miningpoolco" => "MiningPool.co",
      "vaultofsatoshi" => "Vault of Satoshi",
      "smalltimeminer" => "Small Time Miner",
      "smalltimeminer_mec" => "Small Time Miner",
      "ecoining" => "Ecoining",
      "ecoining_ppc" => "Ecoining",
      "teamdoge" => "TeamDoge",
      "dedicatedpool" => "dedicatedpool.com",
      "dedicatedpool_doge" => "dedicatedpool.com",
      "nut2pools" => "Nut2Pools",
      "nut2pools_ftc" => "Nut2Pools",
      "shibepool" => "Shibe Pool",
      "cryptopools" => "CryptoPools",
      "cryptopools_dgc" => "CryptoPools",
      "d2" => "d2",
      "d2_wdc" => "d2",
      "scryptguild" => "ScryptGuild",
      "average" => "Market Average",
      "rapidhash" => "RapidHash",
      "rapidhash_doge" => "RapidHash",
      "rapidhash_vtc" => "RapidHash",
      "cryptotroll" => "Cryptotroll",
      "cryptotroll_doge" => "Cryptotroll",
      "mintpal" => "MintPal",
      "mupool" => "MuPool",
      "ripple" => "Ripple",   // other ledger balances in Ripple accounts are stored as account balances
      "nicehash" => "NiceHash",
      "westhash" => "WestHash",
      "eobot" => "Eobot",
      "hashtocoins" => "Hash-to-Coins",
      "btclevels" => "BTClevels",

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
 */
function get_exchange_pairs() {
  $pairs = array();

  // add all discovered pairs
  foreach (Exchanges::getAllInstances() as $key => $exchange) {
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
  return array(
    "mintpal" => array(array('btc', 'dog'), array('btc', 'ltc'), array('btc', 'vtc'), array('btc', 'bc1'), array('btc', 'drk'),
        array('btc', 'vrc'),
    ),
    "mtgox" => array(array('usd', 'btc'), array('eur', 'btc'), array('aud', 'btc'), array('cad', 'btc'), array('cny', 'btc'), array('gbp', 'btc'), array('pln', 'btc')),
  );
}

$_cached_get_new_exchange_pairs = null;
/**
 * Get all exchange pairs that can be considered 'new'
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

/**
 * Includes disabled exchanges
 */
function get_security_exchange_pairs() {
  return array(
    // should be in alphabetical order
    "796" => array('btc'),
    "bitfunder" => array('btc'),    // this is now disabled
    "btcinve" => array('btc'),
    "btct" => array('btc'),       // issue #93: this is now disabled
    "crypto-trade" => array('btc', 'ltc'),
    "cryptostocks" => array('btc', 'ltc'),
    "litecoinglobal" => array('ltc'),   // issue #93: this is now disabled
    "litecoininvest" => array('ltc'),
    "havelock" => array('btc'),
  );
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
    "anxpro" => array('btc', 'ltc', 'ppc', 'nmc', 'dog', 'usd', 'eur', 'cad', 'aud', 'gbp', 'nzd'),   // also hkd, sgd, jpy, chf
    "bit2c" => array('btc', 'ltc', 'ils'),
    "bitmarket_pl" => array('btc', 'ltc', 'dog', 'ppc', 'pln'),
    "bitminter" => array('btc', 'nmc', 'hash'),
    "bitnz" => array('btc', 'nzd'),
    "bitstamp" => array('btc', 'usd'),
    "bittrex" => array('btc', 'ltc', 'dog', 'vtc', 'ppc', 'bc1', 'drk', 'vrc', 'nxt', 'rdd', 'via'),  // and others, used in jobs/bittrex.php
    "btce" => array('btc', 'ltc', 'nmc', 'usd', 'ftc', 'eur', 'ppc', 'nvc', 'xpm', 'trc'),    // used in jobs/btce.php
    "btcguild" => array('btc', 'nmc', 'hash'),
    "btclevels" => array('btc'),
    "coinbase" => array('btc'),
    "coinhuntr" => array('ltc', 'hash'),
    "cryptopools" => array('dgc', 'hash'),    // other coins available
    "cryptostocks" => array('btc', 'ltc'),
    "crypto-trade" => array('usd', 'eur', 'btc', 'ltc', 'nmc', 'ftc', 'ppc', 'xpm', 'trc', 'dgc', 'wdc', 'bc1', 'dog', 'drk', 'nxt'),
    "cryptotroll" => array('dog', 'hash'),
    "cryptsy" => array('btc', 'ltc', 'ppc', 'ftc', 'xpm', 'nvc', 'trc', 'dog', 'mec', 'ixc', 'nmc', 'wdc', 'dgc', 'vtc', 'net', 'hbn', 'bc1', 'drk', 'nxt', 'rdd', 'via', 'usd', 'vrc', 'xrp'),
    "cexio" => array('btc', 'ghs', 'nmc', 'ixc', 'ltc', 'dog', 'ftc', 'drk', 'mec', 'wdc'),   // also available: dvc
    "d2" => array('wdc', 'hash'),       // other coins available
    "dedicatedpool" => array('dog', 'hash'),    // other coins available
    "ecoining" => array('ppc', 'hash'),
    "eligius" => array('btc', 'hash'),    // BTC is paid directly to BTC address but also stored temporarily
    "elitistjerks" => array('ltc', 'hash'),
    "eobot" => array('btc', 'ltc', 'nmc', 'dog', 'drk', 'ppc', 'nxt', 'hash'),   //  also naut, cure, charity, ghs, scrypt, btsx, sys, ppd
    "ghashio" => array('hash'),   // we only use ghash.io for hashrates
    "givemecoins" => array('ltc', 'vtc', 'ftc', 'ppc', 'dog', 'hash'),
    "havelock" => array('btc'),
    "hashfaster" => array('ltc', 'ftc', 'dog', 'hash'),
    "hashtocoins" => array('dog', 'ltc', 'net', 'nvc', 'wdc', 'hash'),
    "justcoin" => array('btc', 'ltc', 'usd', 'eur', 'xrp'),  // supports btc, usd, eur, nok, ltc
    "khore" => array('nvc', 'hash'),
    "kraken" => array('btc', 'eur', 'ltc', 'nmc', 'usd', 'dog', 'xrp', 'krw', 'gbp'),   // also 'asset-based Ven/XVN'
    "litecoinpool" => array('ltc', 'hash'),
    "litecoininvest" => array('ltc'),
    "liteguardian" => array('ltc'),
    "litepooleu" => array('ltc', 'hash'),
    "kattare" => array('ltc', 'hash'),
    "miningpoolco" => array('dog', 'ltc', 'mec', 'hash'),   // and LOTS more; used in jobs/miningpoolco.php
    "multipool" => array('btc', 'ltc', 'dog', 'ftc', 'ltc', 'nvc', 'ppc', 'trc', 'mec', 'hash'),    // and LOTS more; used in jobs/multipool.php
    "mupool" => array('btc', 'ppc', 'ltc', 'ftc', 'dog', 'vtc', 'hash'),
    "nicehash" => array('btc'),
    "nut2pools" => array('ftc', 'hash'),
    "ozcoin" => array('ltc', 'btc', 'hash'),
    "poloniex" => array('btc', 'ltc', 'dog', 'vtc', 'wdc', 'nmc', 'ppc', 'xpm', 'ixc', 'nxt', 'rdd', 'via', 'nbt', 'xrp', 'ixc', 'mec', 'vrc', 'sj1'),    // and LOTS more; used in jobs/poloniex.php
    "poolx" => array('ltc', 'hash'),
    "scryptpools" => array('dog', 'hash'),
    "teamdoge" => array('dog', 'hash'),
    "triplemining" => array('btc', 'hash'),
    "vaultofsatoshi" => array('cad', 'usd', 'btc', 'ltc', 'ppc', 'dog', 'ftc', 'xpm', 'vtc', 'bc1', 'drk'),   // used in jobs/vaultofsatoshi.php (also supports qrk)
    "vircurex" => array('btc', 'ltc', 'nmc', 'ftc', 'usd', 'eur', 'ppc', 'nvc', 'xpm', 'trc', 'dog', 'ixc', 'vtc', 'nxt'),   // used in jobs/vircurex.php
    "wemineftc" => array('ftc', 'hash'),
    "wemineltc" => array('ltc', 'hash'),
    "westhash" => array('btc'),
    "ypool" => array('ltc', 'xpm', 'dog'),  // also pts
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
    $wallets[$key][] = 'hash';
  }

  return $wallets;
}

// get all supported wallets that are safe w.r.t. allow_unsafe
function get_supported_wallets_safe() {
  $wallets = get_supported_wallets();
  if (!get_site_config('allow_unsafe')) {
    foreach (account_data_grouped() as $label => $group) {
      foreach ($group as $exchange => $value) {
        if (isset($wallets[$exchange]) && $value['unsafe']) {
          unset($wallets[$exchange]);
        }
      }
    }
  }
  return $wallets;
}

function get_new_supported_wallets() {
  return array("bitnz");
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
    case "xpm": return "btce";
    case "trc": return "btce";
    case "dog": return "coins-e";
    case "mec": return "cryptsy";
    case "xrp": return "justcoin";
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

/**
 * Return a grouped array of (job_type => (table, gruop, wizard, failure, ...))
 */
function account_data_grouped() {
  $addresses_data = array();
  $mining_pools_data = array();

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
    );
  }

  foreach (Accounts::getMiners() as $exchange) {
    $mining_pools_data[$exchange] = array(
      'table' => 'accounts_' . $exchange,
      'group' => 'accounts',
      'wizard' => 'pools',
      'failure' => true,
      'disabled' => in_array($exchange, Accounts::getDisabled()),
    );
  }

  $data = array(
    'Addresses' => $addresses_data,
    'Mining pools' => array_merge($mining_pools_data, array(
      'beeeeer' => array('table' => 'accounts_beeeeer', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true, 'disabled' => true),
      'bitminter' => array('table' => 'accounts_bitminter', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'btcguild' => array('table' => 'accounts_btcguild', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'coinhuntr' => array('table' => 'accounts_coinhuntr', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'cryptopools_dgc' => array('table' => 'accounts_cryptopools_dgc', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'cryptopools', 'suffix' => ' DGC'),
      'cryptotroll_doge' => array('table' => 'accounts_cryptotroll_doge', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'cryptotroll', 'suffix' => ' DOGE'),
      'd2_wdc' => array('table' => 'accounts_d2_wdc', 'group' => 'accounts', 'suffix' => ' WDC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'd2'),
      'dedicatedpool_doge' => array('table' => 'accounts_dedicatedpool_doge', 'group' => 'accounts', 'suffix' => ' DOGE', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'dedicatedpool'),
      'dogechainpool' => array('table' => 'accounts_dogechainpool', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true, 'disabled' => true),
      'dogepoolpw' => array('table' => 'accounts_dogepoolpw', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true, 'disabled' => true),
      'ecoining_ppc' => array('table' => 'accounts_ecoining_ppc', 'group' => 'accounts', 'suffix' => ' Peercoin', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'ecoining'),
      'eligius' => array('table' => 'accounts_eligius', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'elitistjerks' => array('table' => 'accounts_elitistjerks', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'eobot' => array('table' => 'accounts_eobot', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'ghashio' => array('table' => 'accounts_ghashio', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'givemecoins' => array('table' => 'accounts_givemecoins', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'hashfaster_doge' => array('table' => 'accounts_hashfaster_doge', 'group' => 'accounts', 'suffix' => ' DOGE', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'hashfaster'),
      'hashfaster_ftc' => array('table' => 'accounts_hashfaster_ftc', 'group' => 'accounts', 'suffix' => ' FTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'hashfaster'),
      'hashfaster_ltc' => array('table' => 'accounts_hashfaster_ltc', 'group' => 'accounts', 'suffix' => ' LTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'hashfaster'),
      'hashtocoins' => array('table' => 'accounts_hashtocoins', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'hypernova' => array('table' => 'accounts_hypernova', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true, 'disabled' => true),
      'kattare' => array('table' => 'accounts_kattare', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'khore' => array('table' => 'accounts_khore', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'lite_coinpool' => array('table' => 'accounts_lite_coinpool', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true, 'disabled' => true),
      'litecoinpool' => array('table' => 'accounts_litecoinpool', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'liteguardian' => array('table' => 'accounts_liteguardian', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'litepooleu' => array('table' => 'accounts_litepooleu', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'ltcmineru' => array('table' => 'accounts_ltcmineru', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true, 'disabled' => true),
      'miningforeman' => array('table' => 'accounts_miningforeman', 'group' => 'accounts', 'suffix' => ' LTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'miningforeman', 'disabled' => true),
      'miningforeman_ftc' => array('table' => 'accounts_miningforeman_ftc', 'group' => 'accounts', 'suffix' => ' FTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'miningforeman', 'disabled' => true),
      'miningpoolco' => array('table' => 'accounts_miningpoolco', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'multipool' => array('table' => 'accounts_multipool', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'mupool' => array('table' => 'accounts_mupool', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'nicehash' => array('table' => 'accounts_nicehash', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'nut2pools_ftc' => array('table' => 'accounts_nut2pools_ftc', 'group' => 'accounts', 'suffix' => ' FTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'nut2pools'),
      'ozcoin_btc' => array('table' => 'accounts_ozcoin_btc', 'group' => 'accounts', 'suffix' => ' BTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'ozcoin'),
      'ozcoin_ltc' => array('table' => 'accounts_ozcoin_ltc', 'group' => 'accounts', 'suffix' => ' LTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'ozcoin'),
      'poolx' => array('table' => 'accounts_poolx', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'rapidhash_doge' => array('table' => 'accounts_rapidhash_doge', 'group' => 'accounts', 'suffix' => ' DOGE', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'rapidhash', 'disabled' => true),
      'rapidhash_vtc' => array('table' => 'accounts_rapidhash_vtc', 'group' => 'accounts', 'suffix' => ' VTC', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'rapidhash', 'disabled' => true),
      'scryptguild' => array('table' => 'accounts_scryptguild', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true, 'disabled' => true),
      'scryptpools' => array('table' => 'accounts_scryptpools', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'shibepool' => array('table' => 'accounts_shibepool', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true, 'disabled' => true),
      'smalltimeminer_mec' => array('table' => 'accounts_smalltimeminer_mec', 'group' => 'accounts', 'suffix' => ' Megacoin', 'wizard' => 'pools', 'failure' => true, 'title_key' => 'smalltimeminer', 'disabled' => true),
      'teamdoge' => array('table' => 'accounts_teamdoge', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'triplemining' => array('table' => 'accounts_triplemining', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'wemineftc' => array('table' => 'accounts_wemineftc', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'wemineltc' => array('table' => 'accounts_wemineltc', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'westhash' => array('table' => 'accounts_westhash', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
      'ypool' => array('table' => 'accounts_ypool', 'group' => 'accounts', 'wizard' => 'pools', 'failure' => true),
    )),
    'Exchanges' => array(
      'anxpro' => array('table' => 'accounts_anxpro', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'bips' => array('table' => 'accounts_bips', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true, 'disabled' => true),
      'bit2c' => array('table' => 'accounts_bit2c', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'bitcurex_eur' => array('table' => 'accounts_bitcurex_eur', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true, 'disabled' => true),
      'bitcurex_pln' => array('table' => 'accounts_bitcurex_pln', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true, 'disabled' => true),
      'btclevels' => array('table' => 'accounts_btclevels', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'bitmarket_pl' => array('table' => 'accounts_bitmarket_pl', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'bitnz' => array('table' => 'accounts_bitnz', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'bitstamp' => array('table' => 'accounts_bitstamp', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'bittrex' => array('table' => 'accounts_bittrex', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'btce' => array('table' => 'accounts_btce', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'cexio' => array('table' => 'accounts_cexio', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'coinbase' => array('table' => 'accounts_coinbase', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'crypto-trade' => array('table' => 'accounts_cryptotrade', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'cryptsy' => array('table' => 'accounts_cryptsy', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'justcoin' => array('table' => 'accounts_justcoin', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'kraken' => array('table' => 'accounts_kraken', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'mtgox' => array('table' => 'accounts_mtgox', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true, 'disabled' => true),
      'poloniex' => array('table' => 'accounts_poloniex', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'vaultofsatoshi' => array('table' => 'accounts_vaultofsatoshi', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
      'vircurex' => array('table' => 'accounts_vircurex', 'group' => 'accounts', 'wizard' => 'exchanges', 'failure' => true),
    ),
    'Securities' => array(
      '796' => array('table' => 'accounts_796', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true),
      'bitfunder' => array('table' => 'accounts_bitfunder', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true, 'disabled' => true),
      'btcinve' => array('table' => 'accounts_btcinve', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true, 'disabled' => true),
      'btct' => array('table' => 'accounts_btct', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true, 'disabled' => true),
      'crypto-trade' => array('table' => 'accounts_cryptotrade', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true),
      'cryptostocks' => array('table' => 'accounts_cryptostocks', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true),
      'havelock' => array('table' => 'accounts_havelock', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true),
      'litecoininvest' => array('table' => 'accounts_litecoininvest', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true),
      'litecoinglobal' => array('table' => 'accounts_litecoinglobal', 'group' => 'accounts', 'wizard' => 'securities', 'failure' => true, 'disabled' => true),
    ),
    'Individual Securities' => array(
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
    'Securities Tickers' => array(
      'securities_796' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_796', 'exchange' => '796', 'securities_table' => 'securities_796'),
      'securities_bitfunder' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_bitfunder', 'exchange' => 'bitfunder', 'securities_table' => 'securities_bitfunder', 'disabled' => true),
      'securities_btcinve' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_btcinve', 'exchange' => 'btcinve', 'securities_table' => 'securities_btcinve', 'disabled' => true),
      'securities_btct' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_btct', 'exchange' => 'btct', 'securities_table' => 'securities_btct', 'disabled' => true),
      'securities_crypto-trade' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_cryptotrade', 'exchange' => 'crypto-trade', 'securities_table' => 'securities_cryptotrade', 'disabled' => true),
      'securities_cryptostocks' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_cryptostocks', 'exchange' => 'cryptostocks', 'securities_table' => 'securities_cryptostocks', 'disabled' => true),
      'securities_havelock' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_havelock', 'exchange' => 'havelock', 'securities_table' => 'securities_havelock', 'failure' => true),
      'securities_litecoininvest' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_litecoininvest', 'exchange' => 'litecoininvest', 'securities_table' => 'securities_litecoininvest'),
      'securities_litecoinglobal' => array('label' => 'security ticker', 'labels' => 'securities', 'table' => 'securities_litecoinglobal', 'exchange' => 'litecoinglobal', 'securities_table' => 'securities_litecoinglobal', 'disabled' => true),
    ),
    'Finance' => array(
      'finance_accounts' => array('title' => 'Finance account', 'label' => 'finance account', 'table' => 'finance_accounts', 'group' => 'finance_accounts', 'job' => false),
      'finance_categories' => array('title' => 'Finance category', 'label' => 'finance category', 'titles' => 'finance categories', 'table' => 'finance_categories', 'group' => 'finance_categories', 'job' => false),
    ),
    'Other' => array(
      'generic' => array('title' => 'Generic APIs', 'label' => 'API', 'table' => 'accounts_generic', 'group' => 'accounts', 'wizard' => 'other', 'failure' => true),
    ),
    'Hidden' => array(
      'graph' => array('title' => 'Graphs', 'table' => 'graphs', 'query' => ' AND is_removed=0', 'job' => false),
      'graph_pages' => array('title' => 'Graph page', 'table' => 'graph_pages', 'group' => 'graph_pages', 'query' => ' AND is_removed=0', 'job' => false),
      'summaries' => array('title' => 'Currency summaries', 'table' => 'summaries', 'group' => 'summaries', 'job' => false),
      'notifications' => array('title' => 'Notifications', 'table' => 'notifications', 'group' => 'notifications', 'wizard' => 'notifications'),
    ),
    'Offsets' => array(
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
      if (!isset($data[$key0][$key]['unsafe'])) {
        $data[$key0][$key]['unsafe'] = false;
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

    $external_apis_addresses["address_" . $key] = $link;
  }

  $external_apis_blockcounts = array();
  foreach (Currencies::getBlockCurrencies() as $key) {
    $currency = Currencies::getInstance($key);
    if ($currency->getExplorerURL()) {
      $link = link_to($currency->getExplorerURL(), $currency->getExplorerName());
    } else {
      $link = htmlspecialchars($currency->getExplorerName());
    }

    $external_apis_blockcounts["blockcount_" . $key] = $link;
  }

  $exchange_tickers = array();
  foreach (Exchanges::getAllInstances() as $key => $exchange) {
    $link = link_to($exchange->getURL(), $exchange->getName());
    $exchange_tickers["ticker_" . $key] = $link;
  }

  $mining_pools = array();
  foreach (Accounts::getMiners() as $key) {
    if (in_array($key, Accounts::getDisabled())) {
      // do not list disabled accounts
      continue;
    }
    $instance = Accounts::getInstance($key);
    $mining_pools[$key] = link_to($instance->getURL(), $instance->getName());
  }

  $external_apis = array(
    "Address balances" => $external_apis_addresses,

    "Block counts" => $external_apis_blockcounts,

    "Mining pool wallets" => array_merge($mining_pools, array(
      'bitminter' => '<a href="https://bitminter.com/">BitMinter</a>',
      'btcguild' => '<a href="https://www.btcguild.com">BTC Guild</a>',
      'coinhuntr' => '<a href="https://coinhuntr.com/">CoinHuntr</a>',
      'cryptopools_dgc' => '<a href="http://dgc.cryptopools.com/">CryptoPools</a> (DGC)',
      'cryptotroll_doge' => '<a href="http://doge.cryptotroll.com">Cryptotroll</a> (DOGE)',
      'd2_wdc' => '<a href="https://wdc.d2.cc/">d2</a> (WDC)',
      'dedicatedpool_doge' => '<a href="http://doge.dedicatedpool.com">dedicatedpool.com</a> (DOGE)',
      'ecoining_ppc' => '<a href="https://peercoin.ecoining.com/">Ecoining Peercoin</a>',
      'eligius' => '<a href="http://eligius.st/">Eligius</a>',
      'elitistjerks' => '<a href="https://www.ejpool.info/">Elitist Jerks</a>',
      'eobot' => '<a href="https://www.eobot.com/">Eobot</a>',
      'ghashio' => '<a href="https://ghash.io">GHash.io</a>',
      'givemecoins' => '<a href="https://www.give-me-coins.com">Give Me Coins</a>',
      'hashfaster_doge' => '<a href="http://doge.hashfaster.com">HashFaster</a> (DOGE)',
      'hashfaster_ftc' => '<a href="http://ftc.hashfaster.com">HashFaster</a> (FTC)',
      'hashfaster_ltc' => '<a href="http://ltc.hashfaster.com">HashFaster</a> (LTC)',
      'hashtocoins' => '<a href="https://hash-to-coins.com/">Hash-to-Coins</a>',
      'kattare' => '<a href="http://ltc.kattare.com/">ltc.kattare.com</a>',
      'khore' => '<a href="https://nvc.khore.org/">nvc.khore.org</a>',
      'liteguardian' => '<a href="https://www.liteguardian.com/">LiteGuardian</a>',
      'litepooleu' => '<a href="http://litepool.eu/">Litepool</a>',
      'miningpoolco' => '<a href="https://www.miningpool.co/">MiningPool.co</a>',
      'multipool' => '<a href="https://multipool.us/">Multipool</a>',
      'mupool' => '<a href="https://mupool.com/">MuPool</a>',
      'nicehash' => '<a href="https://www.nicehash.com/">NiceHash</a>',
      'nut2pools_ftc' => '<a href="https://ftc.nut2pools.com/">Nut2Pools</a> (FTC)',
      'ozcoin_btc' => '<a href="http://ozco.in/">Ozcoin</a> (BTC)',
      'ozcoin_ltc' => '<a href="https://lc.ozcoin.net/">Ozcoin</a> (LTC)',
      'poolx' => '<a href="http://pool-x.eu">Pool-x.eu</a>',
      'scryptpools' => '<a href="http://doge.scryptpools.com">scryptpools.com</a>',
      'securities_update_eligius' => '<a href="http://eligius.st/">Eligius</a> balances',
      'teamdoge' => '<a href="https://teamdoge.com/">TeamDoge</a>',
      'triplemining' => '<a href="https://www.triplemining.com/">TripleMining</a>',
      'wemineftc' => '<a href="https://www.wemineftc.com">WeMineFTC</a>',
      'wemineltc' => '<a href="https://www.wemineltc.com">WeMineLTC</a>',
      'westhash' => '<a href="https://www.westhash.com/">WestHash</a>',
      'ypool' => '<a href="http://ypool.net">ypool.net</a>',
    )),

    "Exchange wallets" => array(
      'anxpro' => '<a href="https://anxpro.com.">ANXPRO</a>',
      'bit2c' => '<a href="https://www.bit2c.co.il">Bit2c</a>',
      'bitmarket_pl' => '<a href="https://www.bitmarket.pl">BitMarket.pl</a>',
      'bitnz' => '<a href="https://bitnz.com">BitNZ</a>',
      'bitstamp' => '<a href="https://www.bitstamp.net">Bitstamp</a>',
      'bittrex' => '<a href="https://bittrex.com/">Bittrex</a>',
      'btce' => '<a href="http://btc-e.com">BTC-e</a>',
      'btclevels' => '<a href="https://btclevels.com/">BTClevels</a>',
      'cexio' => '<a href="https://cex.io">CEX.io</a>',
      'coinbase' => '<a href="https://coinbase.com">Coinbase</a>',
      'crypto-trade' => '<a href="https://www.crypto-trade.com">Crypto-Trade</a>',
      'cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a>',
      'cryptsy' => '<a href="https://www.cryptsy.com/">Crypsty</a>',
      'justcoin' => '<a href="https://justcoin.com/">Justcoin</a>',
      'kraken' => '<a href="https://www.kraken.com/">Kraken</a>',
      'havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a>',
      'litecoininvest' => '<a href="https://litecoininvest.com">Litecoininvest</a>',
      'poloniex' => '<a href="https://www.poloniex.com">Poloniex</a>',
      'vaultofsatoshi' => '<a href="https://www.vaultofsatoshi.com">Vault of Satoshi</a>',
      'vircurex' => '<a href="https://vircurex.com">Vircurex</a>',
    ),

    "Exchange tickers" => $exchange_tickers,

    "Security exchanges" => array(
      'securities_796' => '<a href="https://796.com">796 Xchange</a>',
      'ticker_crypto-trade' => '<a href="https://crypto-trade.com">Crypto-Trade</a>',   // securities for crypto-trade are handled by the ticker_crypto-trade
      'securities_cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a>',
      'securities_havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a>',
      'securities_update_cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a> Securities list',
      'securities_update_havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a> Securities list',
      'securities_update_litecoininvest' => '<a href="https://litecoininvest.com">Litecoininvest</a> Securities list',
    ),

    "Individual securities" => array(
      'individual_crypto-trade' => '<a href="https://crypto-trade.com">Crypto-Trade</a>',
      'individual_cryptostocks' => '<a href="http://cryptostocks.com">Cryptostocks</a>',
      'individual_havelock' => '<a href="https://www.havelockinvestments.com">Havelock Investments</a>',
      'individual_litecoininvest' => '<a href="https://litecoininvest.com">Litecoininvest</a>',
    ),

    "Other" => array(
      // 'generic' => "Generic API balances",
      'outstanding' => '<a href="' . htmlspecialchars(url_for('premium')) . '">Premium account</a> processing',
    ),
  );
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
      $result[$key] = preg_replace('#<[^>]+?>#im', '', $title) . translate_external_api_group_to_suffix($group) . " API";
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
    // --- mining pools ---
    case "poolx":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mmcfe_apikey'),
        ),
        'table' => 'accounts_poolx',
        'khash' => true,
      );

    case "wemineltc":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mmcfe_apikey'),
        ),
        'table' => 'accounts_wemineltc',
        'khash' => true,
      );

    case "wemineftc":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mmcfe_apikey'),
        ),
        'table' => 'accounts_wemineftc',
        'khash' => true,
      );

    case "givemecoins":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mmcfe_apikey'),
        ),
        'table' => 'accounts_givemecoins',
        'khash' => true,
      );

    case "btcguild":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_btcguild_apikey'),
        ),
        'table' => 'accounts_btcguild',
      );

    case "hypernova":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_hypernova_apikey'),
        ),
        'table' => 'accounts_hypernova',
        'khash' => true,
      );

    case "ltcmineru":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_ltcmineru_apikey'),
        ),
        'table' => 'accounts_ltcmineru',
        'khash' => true,
      );

    case "miningforeman":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mmcfe_apikey'),
        ),
        'table' => 'accounts_miningforeman',
        'title' => 'Mining Foreman LTC account',
        'khash' => true,
      );

    case "miningforeman_ftc":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mmcfe_apikey'),
        ),
        'table' => 'accounts_miningforeman_ftc',
        'title' => 'Mining Foreman FTC account',
        'khash' => true,
        'title_key' => 'miningforeman',
      );

    case "bitminter":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_bitminter_apikey'),
        ),
        'table' => 'accounts_bitminter',
      );

    case "liteguardian":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_liteguardian_apikey'),
        ),
        'table' => 'accounts_liteguardian',
        'khash' => true,
      );

    case "khore":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_khore_apikey'),
        ),
        'table' => 'accounts_khore',
        'khash' => true,
      );

    case "kattare":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_kattare_apikey'),
        ),
        'table' => 'accounts_kattare',
        'khash' => true,
      );

    case "litepooleu":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_litepooleu_apikey'),
        ),
        'table' => 'accounts_litepooleu',
        'khash' => true,
      );

    case "coinhuntr":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_coinhuntr_apikey'),
        ),
        'table' => 'accounts_coinhuntr',
        'khash' => true,
      );

    case "eligius":
      return array(
        'inputs' => array(
          'btc_address' => array('title' => 'BTC Address', 'callback' => array(Currencies::getInstance('btc'), 'isValid')),
        ),
        'table' => 'accounts_eligius',
      );

    case "lite_coinpool":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_lite_coinpool_apikey'),
        ),
        'table' => 'accounts_lite_coinpool',
        'khash' => true,
      );

    case "beeeeer":
      return array(
        'inputs' => array(
          'xpm_address' => array('title' => 'XPM Address', 'callback' => 'is_valid_xpm_address'),
        ),
        'table' => 'accounts_beeeeer',
      );

    case "litecoinpool":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_litecoinpool_apikey'),
        ),
        'table' => 'accounts_litecoinpool',
        'khash' => true,
      );

    case "dogepoolpw":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_dogepoolpw_apikey'),
        ),
        'table' => 'accounts_dogepoolpw',
        'khash' => true,
      );

    case "elitistjerks":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_elitistjerks_apikey'),
        ),
        'table' => 'accounts_elitistjerks',
        'khash' => true,
      );

    case "dogechainpool":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_dogechainpool_apikey'),
        ),
        'table' => 'accounts_dogechainpool',
        'khash' => true,
      );

    case "hashfaster_ltc":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
        ),
        'table' => 'accounts_hashfaster_ltc',
        'title' => 'HashFaster LTC account',
        'khash' => true,
        'title_key' => 'hashfaster',
      );

    case "hashfaster_ftc":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
        ),
        'table' => 'accounts_hashfaster_ftc',
        'title' => 'HashFaster FTC account',
        'khash' => true,
        'title_key' => 'hashfaster',
      );

    case "hashfaster_doge":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
        ),
        'table' => 'accounts_hashfaster_doge',
        'title' => 'HashFaster DOGE account',
        'khash' => true,
        'title_key' => 'hashfaster',
      );

    case "triplemining":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_triplemining_apikey'),
        ),
        'table' => 'accounts_triplemining',
      );

    case "ozcoin_ltc":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_ozcoin_ltc_apikey'),
        ),
        'table' => 'accounts_ozcoin_ltc',
        'title' => 'Ozcoin LTC account',
        'khash' => true,
        'title_key' => 'ozcoin',
      );

    case "ozcoin_btc":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_ozcoin_btc_apikey'),
        ),
        'table' => 'accounts_ozcoin_btc',
        'title' => 'Ozcoin BTC account',
        'title_key' => 'ozcoin',
      );

    case "scryptpools":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
        ),
        'table' => 'accounts_scryptpools',
        'khash' => true,
      );

    case "multipool":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_multipool_apikey'),
        ),
        'table' => 'accounts_multipool',
        'khash' => true,    // it's actually both MH/s (BTC) and KH/s (LTC) but we will assume KH/s is more common
      );

    case "ypool":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_ypool_apikey'),
        ),
        'table' => 'accounts_ypool',
        'khash' => true,
      );

    case "miningpoolco":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_miningpoolco_apikey'),
        ),
        'table' => 'accounts_miningpoolco',
        'khash' => true,
      );

    case "smalltimeminer_mec":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
        ),
        'table' => 'accounts_smalltimeminer_mec',
        'title' => 'Small Time Miner Megacoin account',
        'khash' => true,
        'title_key' => 'smalltimeminer',
      );

    case "ecoining_ppc":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
        ),
        'table' => 'accounts_ecoining_ppc',
        'title' => 'Ecoining Peercoin account',
        'title_key' => 'ecoining',
      );

    case "teamdoge":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
        ),
        'table' => 'accounts_teamdoge',
        'khash' => true,
      );

    case "dedicatedpool_doge":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
        ),
        'table' => 'accounts_dedicatedpool_doge',
        'title' => 'dedicatedpool.com DOGE account',
        'title_key' => 'dedicatedpool',
      );

    case "nut2pools_ftc":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
        ),
        'table' => 'accounts_nut2pools_ftc',
        'title' => 'Nut2Pools FTC account',
        'title_key' => 'nut2pools',
      );

    case "shibepool":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
        ),
        'table' => 'accounts_shibepool',
        'khash' => true,
      );

    case "cryptopools_dgc":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
        ),
        'table' => 'accounts_cryptopools_dgc',
        'title' => 'CryptoPools DGC account',
        'khash' => true,
      );

    case "d2_wdc":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mmcfe_apikey'),
        ),
        'table' => 'accounts_d2_wdc',
        'title' => 'd2 DOGE account',
        'khash' => true,
        'title_key' => 'd2',
      );

    case "scryptguild":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_scryptguild_apikey'),
        ),
        'table' => 'accounts_scryptguild',
        'khash' => true,
      );

    case "rapidhash_doge":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
        ),
        'table' => 'accounts_rapidhash_doge',
        'title' => 'RapidHash DOGE account',
        'title_key' => 'rapidhash',
      );

    case "rapidhash_vtc":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
        ),
        'table' => 'accounts_rapidhash_vtc',
        'title' => 'RapidHash VTC account',
        'title_key' => 'rapidhash',
      );

    case "cryptotroll_doge":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
        ),
        'table' => 'accounts_cryptotroll_doge',
        'title' => 'Cryptotroll DOGE account',
        'title_key' => 'cryptotroll',
      );

    case "mupool":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mpos_apikey'),
        ),
        'table' => 'accounts_mupool',
        'khash' => true,
      );

    case "nicehash":
      return array(
        'inputs' => array(
          'api_id' => array('title' => 'API ID', 'callback' => 'is_numeric', 'length' => 16),
          'api_key' => array('title' => 'ReadOnly API Key', 'callback' => 'is_valid_nicehash_apikey'),
        ),
        'table' => 'accounts_nicehash',
        'khash' => true,
      );

    case "westhash":
      return array(
        'inputs' => array(
          'api_id' => array('title' => 'API ID', 'callback' => 'is_numeric', 'length' => 16),
          'api_key' => array('title' => 'ReadOnly API Key', 'callback' => 'is_valid_nicehash_apikey'),
        ),
        'table' => 'accounts_westhash',
        'khash' => true,
      );

    case "eobot":
      return array(
        'inputs' => array(
          'api_id' => array('title' => 'Account ID', 'callback' => 'is_numeric', 'length' => 16),
        ),
        'table' => 'accounts_eobot',
        'khash' => true,    // actually both
      );

    case "hashtocoins":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API Key', 'callback' => 'is_valid_hashtocoins_apikey'),
        ),
        'table' => 'accounts_hashtocoins',
        'khash' => true,
      );

    // --- exchanges ---
    case "mtgox":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_mtgox_apikey'),
          'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_mtgox_apisecret', 'length' => 128),
        ),
        'table' => 'accounts_mtgox',
        'title' => 'Mt.Gox account',
      );

    case "bips":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_bips_apikey'),
        ),
        'table' => 'accounts_bips',
      );

    case "bit2c":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_bit2c_apikey'),
          'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_bit2c_apisecret', 'length' => 128),
        ),
        'table' => 'accounts_bit2c',
        'title' => 'Bit2c account',
      );

    case "bitcurex_pln":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_bitcurex_pln_apikey'),
          'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_bitcurex_pln_apisecret', 'length' => 128),
        ),
        'table' => 'accounts_bitcurex_pln',
      );

    case "bitcurex_eur":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_bitcurex_eur_apikey'),
          'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_bitcurex_eur_apisecret', 'length' => 128),
        ),
        'table' => 'accounts_bitcurex_eur',
      );

    case "btce":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_btce_apikey'),
          'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_btce_apisecret'),
        ),
        'table' => 'accounts_btce',
      );

    case "vircurex":
      return array(
        'inputs' => array(
          'api_username' => array('title' => 'Username', 'callback' => 'is_valid_vircurex_apiusername'),
          'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_vircurex_apisecret', 'length' => 128),
        ),
        'table' => 'accounts_vircurex',
      );

    case "cexio":
      return array(
        'inputs' => array(
          'api_username' => array('title' => 'Username', 'callback' => 'is_valid_cexio_apiusername'),
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_cexio_apikey'),
          'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_cexio_apisecret', 'length' => 32),
        ),
        'table' => 'accounts_cexio',
      );

    case "ghashio":
      return array(
        'inputs' => array(
          'api_username' => array('title' => 'Username', 'callback' => 'is_valid_cexio_apiusername'),
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_cexio_apikey'),
          'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_cexio_apisecret', 'length' => 32),
        ),
        'table' => 'accounts_ghashio',
      );

    case "crypto-trade":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_cryptotrade_apikey'),
          'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_cryptotrade_apisecret'),
        ),
        'table' => 'accounts_cryptotrade',
      );

    case "bitstamp":
      return array(
        'inputs' => array(
          'api_client_id' => array('title' => 'Customer ID', 'callback' => 'is_numeric'),
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_bitstamp_apikey'),
          'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_bitstamp_apisecret', 'length' => 32),
        ),
        'table' => 'accounts_bitstamp',
      );

    case "justcoin":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_justcoin_apikey'),
        ),
        'table' => 'accounts_justcoin',
      );

    case "cryptsy":
      return array(
        'inputs' => array(
          'api_public_key' => array('title' => 'Application key', 'callback' => 'is_valid_cryptsy_public_key', 'length' => 40),
          'api_private_key' => array('title' => 'App ID', 'callback' => 'is_valid_cryptsy_private_key', 'length' => 80),
        ),
        'table' => 'accounts_cryptsy',
      );

    case "coinbase":
      return array(
        'inputs' => array(
          // we don't expose api_code here; this is obtained through the OAuth2 callback
        ),
        'table' => 'accounts_coinbase',
      );

    case "vaultofsatoshi":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_vaultofsatoshi_apikey'),
          'api_secret' => array('title' => 'API secret key', 'callback' => 'is_valid_vaultofsatoshi_apisecret'),
        ),
        'table' => 'accounts_vaultofsatoshi',
      );

    case "kraken":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_kraken_apikey'),
          'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_kraken_apisecret', 'length' => 128),
        ),
        'table' => 'accounts_kraken',
      );

    case "bitmarket_pl":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_bitmarket_pl_apikey'),
          'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_bitmarket_pl_apisecret', 'length' => 128),
        ),
        'table' => 'accounts_bitmarket_pl',
      );

    case "poloniex":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_poloniex_apikey'),
          'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_poloniex_apisecret', 'length' => 128),
          'accept' => array('title' => 'I accept that this API is unsafe', 'checkbox' => true, 'callback' => 'number_format'),
        ),
        'unsafe' => "A Poloniex API key allows trading, but does not allow withdrawl.",
        'table' => 'accounts_poloniex',
      );

    case "anxpro":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'Key', 'callback' => 'is_valid_anxpro_apikey'),
          'api_secret' => array('title' => 'Secret', 'callback' => 'is_valid_anxpro_apisecret', 'length' => 128),
        ),
        'table' => 'accounts_anxpro',
      );

    case "bittrex":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_bittrex_apikey'),
          'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_bittrex_apisecret', 'length' => 128),
        ),
        'table' => 'accounts_bittrex',
      );

    case "btclevels":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_string'),
          'api_secret' => array('title' => 'API secret', 'callback' => 'is_valid_btclevels_apisecret', 'length' => 128),
        ),
        'table' => 'accounts_btclevels',
      );

    case "bitnz":
      return array(
        'inputs' => array(
          'api_username' => array('title' => 'Username', 'callback' => 'is_string'),
          'api_key' => array('title' => 'API Key', 'callback' => 'is_valid_bitnz_apikey'),
          'api_secret' => array('title' => 'API Secret', 'callback' => 'is_valid_bitnz_apisecret', 'length' => 32),
        ),
        'table' => 'accounts_bitnz',
      );

    // --- securities ---
    case "btct":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'Read-Only API key', 'callback' => 'is_valid_btct_apikey'),
        ),
        'table' => 'accounts_btct',
      );

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

    case "havelock":
      return array(
        'inputs' => array(
          'api_key' => array('title' => 'API key', 'callback' => 'is_valid_havelock_apikey'),
        ),
        'table' => 'accounts_havelock',
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
        }

        return array(
          'inputs' => $inputs,
          'table' => 'accounts_' . $exchange,
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
  return dropdown_get_all_securities('securities_litecoinglobal');
}

function dropdown_get_btct_securities() {
  return dropdown_get_all_securities('securities_btct');
}

function dropdown_get_bitfunder_securities() {
  return dropdown_get_all_securities('securities_bitfunder');
}

function dropdown_get_cryptostocks_securities() {
  return dropdown_get_all_securities('securities_cryptostocks');
}

function dropdown_get_havelock_securities() {
  return dropdown_get_all_securities('securities_havelock');
}

function dropdown_get_cryptotrade_securities() {
  return dropdown_get_all_securities('securities_cryptotrade' /* table */);
}

function dropdown_get_796_securities() {
  return dropdown_get_all_securities('securities_796', 'title');
}

function dropdown_get_litecoininvest_securities() {
  return dropdown_get_all_securities('securities_litecoininvest');
}

function dropdown_get_btcinve_securities() {
  return dropdown_get_all_securities('securities_btcinve');
}

/**
 * Returns an array of (id => security name).
 * Cached across calls.
 */
$dropdown_get_all_securities = array();
function dropdown_get_all_securities($table, $title_key = 'name') {
  global $dropdown_get_all_securities;
  if (!isset($dropdown_get_all_securities[$table])) {
    $dropdown_get_all_securities[$table] = array();
    $q = db()->prepare("SELECT id, $title_key AS name FROM " . $table);
    $q->execute();
    while ($sec = $q->fetch()) {
      $dropdown_get_all_securities[$table][$sec['id']] = $sec['name'];
    }
  }
  return $dropdown_get_all_securities[$table];
}

function is_valid_mmcfe_apikey($key) {
  // not sure what the format should be, seems to be 64 character hexadecmial
  return strlen($key) == 64 && preg_match("#^[a-z0-9]+$#", $key);
}

function is_valid_bit2c_apikey($key) {
  // not sure what the format should be
  return preg_match("#^[a-z0-9]+-[a-z0-9]+-[a-z0-9]+-[a-z0-9]+-[a-z0-9]+$#", $key);
}

function is_valid_bit2c_apisecret($key) {
  // not sure what the format should be
  return strlen($key) == 64 && preg_match("#^[a-z0-9]+$#", $key);
}

function is_valid_btce_apikey($key) {
  // not sure what the format should be
  return strlen($key) == 44 && preg_match("#^[A-Z0-9\-]+$#", $key);
}

function is_valid_btce_apisecret($key) {
  // not sure what the format should be
  return strlen($key) == 64 && preg_match("#^[a-z0-9]+$#", $key);
}

function is_valid_mtgox_apikey($key) {
  // not sure what the format should be
  return strlen($key) == 36 && preg_match("#^[a-z0-9\-]+$#", $key);
}

function is_valid_mtgox_apisecret($key) {
  // not sure what the format should be, looks to be similar to base64 encoding
  return strlen($key) > 36 && preg_match('#^[A-Za-z0-9/\\+=]+$#', $key);
}

function is_valid_litecoinglobal_apikey($key) {
  // not sure what the format should be, seems to be 64 character hex
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_btct_apikey($key) {
  // not sure what the format should be, seems to be 64 character hex
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_vircurex_apiusername($key) {
  // this could probably be in any format but should be at least one character
  return strlen($key) >= 1 && strlen($key) <= 255;
}

function is_valid_vircurex_apisecret($key) {
  // this could probably be in any format but should be at least one character
  return strlen($key) >= 1 && strlen($key) <= 255;
}

function is_valid_havelock_apikey($key) {
  // not sure what the format is, but it looks to be 64 characters of random alphanumeric
  return preg_match("#^[0-9A-Za-z]{64}$#", $key);
}

function is_valid_bips_apikey($key) {
  // looks like a 32 character hex string
  return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_btcguild_apikey($key) {
  // looks like a 32 character hex string
  return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_hypernova_apikey($key) {
  // looks like a 64 character hex string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_ltcmineru_apikey($key) {
  // looks like a username, followed by 32 character hex string
  return preg_match("#^.+_[a-f0-9]{32}$#", $key);
}

function is_valid_generic_key($key) {
  // this could probably be in any format but should be at least one character
  return strlen($key) >= 1 && strlen($key) <= 255;
}

function is_valid_bitminter_apikey($key) {
  // looks like a 32 character alphanumeric uppercase string
  return strlen($key) == 32 && preg_match("#^[A-Z0-9]+$#", $key);
}

function is_valid_liteguardian_apikey($key) {
  // looks like 'api', followed by 32 character hex string
  return preg_match("#^api[a-f0-9]{32}$#", $key);
}

function is_valid_khore_apikey($key) {
  // looks like a 64 character hex string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_kattare_apikey($key) {
  // looks like a 64 character hex string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_cexio_apikey($key) {
  // looks like a 20-32 character alphanumeric mixed case string
  return strlen($key) >= 20 && strlen($key) <= 32 && preg_match("#^[A-Za-z0-9]+$#", $key);
}

function is_valid_cexio_apisecret($key) {
  // looks like a 20-32 character alphanumeric mixed case string
  return strlen($key) >= 20 && strlen($key) <= 32 && preg_match("#^[A-Za-z0-9]+$#", $key);
}

function is_valid_cexio_apiusername($key) {
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

function is_valid_bitstamp_apikey($key) {
  // looks like a 32 character alphanumeric string
  return strlen($key) == 32 && preg_match("#^[A-Za-z0-9]+$#", $key);
}

function is_valid_bitstamp_apisecret($key) {
  // looks like a 32 character alphanumeric string
  return strlen($key) == 32 && preg_match("#^[A-Za-z0-9]+$#", $key);
}

function is_valid_796_apikey($key) {
  // guessing the format
  return preg_match("#^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{8}$#", $key);
}

function is_valid_796_apisecret($key) {
  // looks like a 60 character crazy string
  return strlen($key) == 60 && preg_match("#^[A-Za-z0-9\\+\\/]+$#", $key);
}

function is_valid_litepooleu_apikey($key) {
  // looks like a 64 character hex string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_coinhuntr_apikey($key) {
  // looks like a 64 character hex string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_lite_coinpool_apikey($key) {
  // looks like a 64 character hex string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_litecoinpool_apikey($key) {
  // looks like a 32 character hex string
  return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_dogepoolpw_apikey($key) {
  // looks like a 64 character hex string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_elitistjerks_apikey($key) {
  // looks like a 64 character hex string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_dogechainpool_apikey($key) {
  // looks like a 64 character hex string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_triplemining_apikey($key) {
  // looks like a 32 character hex string
  return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_ozcoin_ltc_apikey($key) {
  // guessing the format
  return preg_match("#^[0-9]+_[a-zA-Z]+$#", $key);
}

function is_valid_ozcoin_btc_apikey($key) {
  // guessing the format
  return preg_match("#^[0-9]+_[a-zA-Z]+$#", $key);
}

function is_valid_bitcurex_pln_apikey($key) {
  // looks like a 64 character hex string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_bitcurex_pln_apisecret($key) {
  // looks like a long base64 encoded string
  return strlen($key) > 60 && strlen($key) < 100 && preg_match("#^[a-zA-Z0-9/\\+=]+$#", $key);
}

function is_valid_bitcurex_eur_apikey($key) {
  return is_valid_bitcurex_pln_apikey($key);
}

function is_valid_bitcurex_eur_apisecret($key) {
  return is_valid_bitcurex_pln_apisecret($key);
}

function is_valid_justcoin_apikey($key) {
  // looks like a 64 character hex string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_multipool_apikey($key) {
  // looks like a 64 character hex string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_ypool_apikey($key) {
  // looks like a 20 character string of almost any characters
  return strlen(trim($key)) == 20;
}

function is_valid_cryptsy_public_key($key) {
  // looks like a 40 character hex string (full trade) or 18-19 characters (application keys)
  return (strlen($key) >= 16 || strlen($key) <= 40) && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_cryptsy_private_key($key) {
  // can be anything
  return strlen($key) > 0;
}

function is_valid_litecoininvest_apikey($key) {
  // looks to be lowercase hex
  return preg_match("#^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$#", $key);
}

function is_valid_miningpoolco_apikey($key) {
  // looks like a 40 character hex string
  return strlen($key) == 40 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_vaultofsatoshi_apikey($key) {
  // looks like a 64 character alphanumeric string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_vaultofsatoshi_apisecret($key) {
  // looks like a 64 character alphanumeric string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_mpos_apikey($key) {
  // looks like a 64 character hex string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_scryptguild_apikey($key) {
  // looks like a 32 character hex string
  return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_kraken_apikey($key) {
  return strlen($key) == 56 && preg_match("#^[a-zA-Z0-9=/+]+$#", $key);
}

function is_valid_kraken_apisecret($key) {
  return strlen($key) > 64 && strlen($key) < 128 && preg_match("#^[a-zA-Z0-9=/+]+$#", $key);
}

function is_valid_bitmarket_pl_apikey($key) {
  // looks like a 32 character hex string
  return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_bitmarket_pl_apisecret($key) {
  // looks like a 32 character hex string
  return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_poloniex_apikey($key) {
  // looks like 4 sets of 8 characters
  return preg_match("#^[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}+$#", $key);
}

function is_valid_poloniex_apisecret($key) {
  // looks like a 128 character hex string
  return strlen($key) == 128 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_anxpro_apikey($key) {
  // not sure what the format should be
  return strlen($key) == 36 && preg_match("#^[a-z0-9\-]+$#", $key);
}

function is_valid_anxpro_apisecret($key) {
  // not sure what the format should be, looks to be similar to base64 encoding
  return strlen($key) > 36 && preg_match('#^[A-Za-z0-9/\\+=]+$#', $key);
}

function is_valid_bittrex_apisecret($key) {
  // looks like a 32 character hex string
  return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_bittrex_apikey($key) {
  // looks like a 32 character hex string
  return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_nicehash_apikey($key) {
  return preg_match("#^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$#", $key);
}

function is_valid_hashtocoins_apikey($key) {
  // looks like a 64 character hex string
  return strlen($key) == 64 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_btclevels_apisecret($key) {
  return preg_match("#^[a-z0-9]{16}-[a-z0-9]{16}-[a-z0-9]{16}-[a-z0-9]{16}-[a-z0-9]{16}$#", $key);
}

function is_valid_bitnz_apikey($key) {
  // looks like a 32 character hex string
  return strlen($key) == 32 && preg_match("#^[a-f0-9]+$#", $key);
}

function is_valid_bitnz_apisecret($key) {
  // looks like a random string
  return strlen($key) > 8 && strlen($key) < 12 && preg_match("#^[a-zA-Z0-9]+$#", $key);
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
