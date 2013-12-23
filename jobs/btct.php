<?php

/**
 * BTC Trading Co. balance job.
 * Combines the current wallet balance with the value of all securities from this account
 * (security values are done by securities_btct).
 */

$exchange = "btct";
$currency = 'btc';

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_btct WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

$content = crypto_get_contents(crypto_wrap_url('https://btct.co/api/act?key=' . urlencode($account['api_key'])));
if (!$content) {
	throw new ExternalAPIException("API returned empty data");
}

// fix broken JSON
$content = preg_replace("#<!--[^>]+-->#", "", $content);

$data = crypto_json_decode($content);

// account balance
if (isset($data['balance']['LTC'])) {
	throw new ExternalAPIException("API key was for LTC, not BTC.");
}
$wallet = $data['balance'][strtoupper($currency)];
$balance = 0;

// set is_recent=0 for all old security instances for this user
$q = db()->prepare("UPDATE securities SET is_recent=0 WHERE user_id=? AND exchange=? AND account_id=?");
$q->execute(array($job['user_id'], $exchange, $account['id']));

// and for each security
foreach ($data['securities'] as $security => $detail) {
	// make sure that a security definition exists
	$q = db()->prepare("SELECT * FROM securities_btct WHERE name=?");
	$q->execute(array($security));
	$security_def = $q->fetch();
	if (!$security_def) {
		// need to insert a new security definition, so we can later get its value
		// we can't calculate the value of this security yet
		crypto_log("No securities_btct definition existed for '" . htmlspecialchars($security) . "': adding in new definition");
		$q = db()->prepare("INSERT INTO securities_btct SET name=?");
		$q->execute(array($security));
		$security_def = array('id' => db()->lastInsertId());

	} else {
		// the 'balance' for this security is the 'bid'
		$q = db()->prepare("SELECT * FROM balances WHERE exchange=:exchange AND account_id=:account_id AND is_recent=1 LIMIT 1");
		$q->execute(array(
			"exchange" => "securities_btct",
			"account_id" => $security_def['id'],
		));
		$security_value = $q->fetch();
		if (!$security_value) {
			// we can't calculate the value of this security yet
			crypto_log("Security " . htmlspecialchars($security) . " does not yet have a calculated value");

		} else {

			$calculated = $security_value['balance'] * $detail['quantity'];
			crypto_log(htmlspecialchars($security) . " @ " . htmlspecialchars($security_value['balance']) . " x " . number_format($detail['quantity']) . " = " . htmlspecialchars($calculated));

			$balance += $calculated;

		}

	}

	// insert security instance
	$q = db()->prepare("INSERT INTO securities SET user_id=:user_id, exchange=:exchange, security_id=:security_id, quantity=:quantity, account_id=:account_id, is_recent=1");
	$q->execute(array(
		'user_id' => $job['user_id'],
		'exchange' => $exchange,
		'security_id' => $security_def['id'],
		'quantity' => $detail['quantity'],
		'account_id' => $account['id'],
	));

}

// we've now calculated both the wallet balance + the value of all securities
insert_new_balance($job, $account, $exchange . '_wallet', $currency, $wallet);
insert_new_balance($job, $account, $exchange . '_securities', $currency, $balance);

