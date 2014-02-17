<?php

/**
 * MiningPool.co mining pool balance job.
 */

$exchange = "miningpoolco";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_miningpoolco WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

crypto_log($account['api_key']);
$data = crypto_json_decode(crypto_get_contents(crypto_wrap_url("https://www.miningpool.co/api/balances?key=" . urlencode($account['api_key']))));

if (isset($data['error']) && $data['error']) {
	throw new ExternalAPIException(htmlspecialchars($data['error']));
}
if (!isset($data['return']) || !$data['return']) {
	throw new ExternalAPIException("API did not return anything");
}

$wallets = get_supported_wallets();
foreach ($wallets['miningpoolco'] as $currency) {
	if ($currency == 'hash') {
		// this is just a marker
		continue;
	}

	$found = false;
	foreach ($data['return'] as $row) {
		if ($row['short_code'] == get_currency_abbr($currency)) {
			$found = true;
			if ($row['balance']) {
				insert_new_balance($job, $account, $exchange, $currency, $row['balance']);
			} else {
				crypto_log("No balance found for " . get_currency_abbr($currency) . ".");
			}
			if ($row['hashrate']) {
				insert_new_hashrate($job, $account, $exchange, $currency, $row['hashrate'] / (is_hashrate_mhash($currency) ? 1 : 1000) /* not sure if API returns MH/s for SHA-256 currencies */);
			} else {
				crypto_log("No hashrate found for " . get_currency_abbr($currency) . ".");
			}
		}
	}

	if (!$found) {
		crypto_log("Did not find any data for currency " . get_currency_abbr($currency) . ".");
	}

}
