<?php

/**
 * BTCInve balance job.
 * Combines the current wallet balance with the value of all securities from this account
 * (security values are done by securities_btcinve).
 */

$exchange = "btcinve";
$currency = 'btc';

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_btcinve WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

// first, get balances
$data = crypto_json_decode(crypto_get_contents(crypto_wrap_url('https://www.btcinve.com/api/act?key=' . urlencode($account['api_key']))));
if (!isset($data['balance'][get_currency_abbr($currency)])) {
	throw new ExternalAPIException("No " . get_currency_abbr($currency) . " balance found");
}
$wallet = $data['balance'][get_currency_abbr($currency)];

// -- and now get securities --

// set is_recent=0 for all old security instances for this user
$q = db()->prepare("UPDATE securities SET is_recent=0 WHERE user_id=? AND exchange=? AND account_id=?");
$q->execute(array($job['user_id'], $exchange, $account['id']));

$balance = 0;
$data = crypto_json_decode(crypto_get_contents(crypto_wrap_url('https://www.btcinve.com/api/private/portfolio?key=' . urlencode($account['api_key']))), false /* message */, true /* empty array is ok */);
foreach ($data as $row) {
	$security = $row['ticker'];
	$bid = $row['bid'];		// also available: avg_buy_price

	// make sure that a security definition exists
	$q = db()->prepare("SELECT * FROM securities_btcinve WHERE name=?");
	$q->execute(array($security));
	$security_def = $q->fetch();
	if (!$security_def) {
		// need to insert a new security definition, so we can later get its value
		// we can't calculate the value of this security yet
		crypto_log("No securities_btcinve definition existed for '" . htmlspecialchars($security) . "': adding in new definition");
		$q = db()->prepare("INSERT INTO securities_btcinve SET name=?");
		$q->execute(array($security));
		$security_def = array('id' => db()->lastInsertId());

	}

	// insert in a new balance
	$job2 = $job;
	$job2['user_id'] = get_site_config('system_user_id'); /* need to insert security values as system user, or else they won't be displayed in a graph! */
	insert_new_balance($job2, $security_def, "securities_" . $exchange, $currency, $bid);

	$calculated = $bid * $row['quantity'];
	crypto_log(htmlspecialchars($security) . " @ " . htmlspecialchars($bid) . " x " . number_format($row['quantity']) . " = " . htmlspecialchars($calculated));

	$balance += $calculated;

	// insert security instance
	$q = db()->prepare("INSERT INTO securities SET user_id=:user_id, exchange=:exchange, security_id=:security_id, quantity=:quantity, account_id=:account_id, is_recent=1");
	$q->execute(array(
		'user_id' => $job['user_id'],
		'exchange' => $exchange,
		'security_id' => $security_def['id'],
		'quantity' => $row['quantity'],
		'account_id' => $account['id'],
	));

}

// we've now calculated both the wallet balance + the value of all securities
insert_new_balance($job, $account, $exchange . '_wallet', $currency, $wallet);
insert_new_balance($job, $account, $exchange . '_securities', $currency, $balance);
