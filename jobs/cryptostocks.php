<?php

/**
 * Cryptostocks balance job.
 * Combines the current wallet balance with the value of all securities from this account
 * (security values are done by securities_cryptostocks).
 */

$exchange = "cryptostocks";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_cryptostocks WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

function cryptostocks_api($key, $email, $method) {
	$token_id = rand(0,0xffff);
	$request_time = time();
	$token = $key . ";" .$email . ";" . $request_time . ";" . $token_id . ";" . $method;

	$url = url_add("https://cryptostocks.com/api/" . urlencode($method) . ".json", array('account' => $email, 'id' => $token_id, 'token' => hash('sha256', $token), 'timestamp' => $request_time));

	$content = crypto_get_contents(crypto_wrap_url($url));
	if (!$content) {
		throw new ExternalAPIException("API returned empty data");
	}

	$data = crypto_json_decode($content);

	return $data;
}

$balances = array(
	'btc' => 0,
	'ltc' => 0,
	// 'dvc' => 0,
);
$wallets = array(
	'btc' => 0,
	'ltc' => 0,
	// 'dvc' => 0,
);

// first, get coin balance
$get_coin_balance = cryptostocks_api($account['api_key_coin'], $account['api_email'], 'get_coin_balances');

foreach ($balances as $currency => $ignored) {
	if (isset($get_coin_balance[strtoupper($currency)]['balance'])) {
		$wallets[$currency] += $get_coin_balance[strtoupper($currency)]['balance'];
		crypto_log("Balance for $currency: " . $get_coin_balance[strtoupper($currency)]['balance'] . " " . strtoupper($currency));
	}
}

// wait some time
sleep(get_site_config('sleep_cryptostocks_balance'));

// and now get the value of all securities
// relies on securities_cryptostocks
$get_share_balances = cryptostocks_api($account['api_key_share'], $account['api_email'], 'get_share_balances');

// set is_recent=0 for all old security instances for this user
$q = db()->prepare("UPDATE securities SET is_recent=0 WHERE user_id=? AND exchange=? AND account_id=?");
$q->execute(array($job['user_id'], $exchange, $account['id']));

foreach ($get_share_balances['tickers'] as $security) {
	if (!isset($get_share_balances[$security]['balance'])) {
		throw new ExternalAPIException("Specified security " . htmlspecialchars($security) . " had no balance");
	}
	$shares = $get_share_balances[$security]['balance'];

	// make sure that a security definition exists
	$q = db()->prepare("SELECT * FROM securities_cryptostocks WHERE name=?");
	$q->execute(array($security));
	$security_def = $q->fetch();
	if (!$security_def) {
		// need to insert a new security definition, so we can later get its value
		// we can't calculate the value of this security yet
		crypto_log("No securities_cryptostocks definition existed for '" . htmlspecialchars($security) . "': adding in new definition");
		$q = db()->prepare("INSERT INTO securities_cryptostocks SET name=?");
		$q->execute(array($security));
		$security_def = array('id' => db()->lastInsertId());

	} else if (!isset($balances[$security_def['currency']])) {
		// this allows us to safely ignore securities in other currencies
		crypto_log("Security $security is not a currently recognised currency: " . $security_def['currency']);

	} else {
		// the 'balance' for this security is the 'bid'
		$q = db()->prepare("SELECT * FROM balances WHERE exchange=:exchange AND account_id=:account_id AND is_recent=1 LIMIT 1");
		$q->execute(array(
			"exchange" => "securities_cryptostocks",
			"account_id" => $security_def['id'],
		));
		$security_value = $q->fetch();
		if (!$security_value) {
			// we can't calculate the value of this security yet
			crypto_log("Security " . htmlspecialchars($security) . " does not yet have a calculated value");

		} else {

			$calculated = $security_value['balance'] * $shares;
			crypto_log(htmlspecialchars($security) . " @ " . htmlspecialchars($security_value['balance']) . " x " . number_format($shares) . " = " . htmlspecialchars($calculated) . " " . strtoupper($security_def['currency']));

			$balances[$security_def['currency']] += $calculated;

		}
	}

	// insert security instance
	// but only if we actually have a quantity
	if ($shares != 0) {
		$q = db()->prepare("INSERT INTO securities SET user_id=:user_id, exchange=:exchange, security_id=:security_id, quantity=:quantity, account_id=:account_id, is_recent=1");
		$q->execute(array(
			'user_id' => $job['user_id'],
			'exchange' => $exchange,
			'security_id' => $security_def['id'],
			'quantity' => $shares,
			'account_id' => $account['id'],
		));
	}
}

// we've now calculated both the wallet balance + the value of all securities
foreach ($wallets as $currency => $balance) {

	insert_new_balance($job, $account, $exchange . '_wallet', $currency, $balance);

}
foreach ($balances as $currency => $balance) {

	insert_new_balance($job, $account, $exchange . '_securities', $currency, $balance);

}
