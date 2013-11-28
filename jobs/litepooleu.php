<?php

/**
 * Litepool (litepool.eu) pool balance job.
 * Uses php-mpos mining pool; if more pools are using this software, it would be possible to
 * refactor this (as with _mmcfe_pool.php)
 */

$exchange = "litepooleu";
$currency = 'ltc';

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_litepooleu WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// get balance
$data = json_decode(crypto_get_contents(crypto_wrap_url("http://litepool.eu/index.php?page=api&action=getuserbalance&api_key=" . $account['api_key'])), true);
if ($data === null) {
	throw new ExternalAPIException("Invalid JSON detected on getuserbalance");
} else {
	if (!isset($data['getuserbalance']['data']['confirmed'])) {
		throw new ExternalAPIException("No confirmed balance found");
	}

	insert_new_balance($job, $account, $exchange, $currency, $data['getuserbalance']['data']['confirmed']);
}

// get hashrate
$data = json_decode(crypto_get_contents(crypto_wrap_url("http://litepool.eu/index.php?page=api&action=getuserstatus&api_key=" . $account['api_key'])), true);
if ($data === null) {
	throw new ExternalAPIException("Invalid JSON detected on getuserstatus");
} else {
	if (!isset($data['getuserstatus']['data']['hashrate'])) {
		throw new ExternalAPIException("No hashrate found");
	}

	insert_new_hashrate($job, $account, $exchange, $currency, $data['getuserstatus']['data']['hashrate'] / 1000 /* assume response is in KH/s */);
}
