<?php

/**
 * Hash-to-coins mining pool balance job.
 */

$exchange = "hashtocoins";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_hashtocoins WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$raw = crypto_get_contents(crypto_wrap_url("https://hash-to-coins.com/index.php?page=api&action=getuserbalances&api_key=" . urlencode($account['api_key'])));
$data = crypto_json_decode($raw);

if (!isset($data['getuserbalances']['data'])) {
	throw new ExternalAPIException("Could not find any balances");
}

$wallets = get_supported_wallets();

foreach ($data['getuserbalances']['data'] as $row) {
	foreach ($wallets['hashtocoins'] as $currency) {
		if ($row['tag'] == get_currency_abbr($currency)) {
			$balance = $row['confirmed'];

			insert_new_balance($job, $account, $exchange, $currency, $balance);
		}
	}
}

// and now hashrates
$raw = crypto_get_contents(crypto_wrap_url("https://hash-to-coins.com/index.php?page=api&action=getuserhashrate&api_key=" . urlencode($account['api_key'])));
$data = crypto_json_decode($raw);

if (!isset($data['getuserhashrate']['data'])) {
	throw new ExternalAPIException("Could not find a hashrate");
}

$hash_rate = $data['getuserhashrate']['data'] / 1000 /* in H/s */;

// NOTE this assumes the user is always mining LTC; this isn't true, but this should
// make it easier to track autoswitching miners
insert_new_hashrate($job, $account, $exchange, 'ltc', $hash_rate / 1000 /* KH/s */);
