<?php

/**
 * Eobot mining pool balance job.
 */

$exchange = "eobot";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_eobot WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$raw = crypto_get_contents(crypto_wrap_url("https://www.eobot.com/api.aspx?total=" . urlencode($account['api_id'])));
// convert into an object
$data = array();
$split = explode(";", $raw);
foreach ($split as $row) {
	if (!$row) continue;
	$bits = explode(":", $row, 2);
	$data[$bits[0]] = $bits[1];
}

$wallets = get_supported_wallets();
foreach ($wallets['eobot'] as $currency) {
	if ($currency == "hash") {
		continue;
	}

	if (!isset($data[get_currency_abbr($currency)])) {
		throw new ExternalAPIException("Did not find any balance for " . get_currency_abbr($currency));
	}

	$balance = $data[get_currency_abbr($currency)];

	insert_new_balance($job, $account, $exchange, $currency, $balance);
}

/// hashrates
$raw = crypto_get_contents(crypto_wrap_url("https://www.eobot.com/api.aspx?idspeed=" . urlencode($account['api_id'])));
// convert into an object
$data = array();
$split = explode(";", $raw);
foreach ($split as $row) {
	if (!$row) continue;
	$bits = explode(":", $row, 2);
	$data[$bits[0]] = $bits[1];
}

$wallets = get_supported_wallets();
foreach ($wallets['eobot'] as $currency) {
  if ($currency == "hash") {
    continue;
  }

  if (isset($data['MiningSHA-256']) && is_hashrate_mhash($currency)) {
  	$hash_rate = $data['MiningSHA-256'] + $data['CloudSHA-256'];
  	insert_new_hashrate($job, $account, $exchange . "_sha", $currency, $hash_rate);
  }

  if (isset($data['MiningScrypt']) && !is_hashrate_mhash($currency)) {
  	$hash_rate = $data['MiningScrypt'] + $data['CloudScrypt'];
  	insert_new_hashrate($job, $account, $exchange . "_scrypt", $currency, $hash_rate / 1000 /* in KHash/s */);
  }
}
